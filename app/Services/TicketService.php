<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketActivity;
use App\Repositories\TicketRepository;
use App\Enums\TicketStatus;
use App\Events\TicketCreated;
use App\Events\TicketAssigned;
use App\Events\TicketStatusChanged;
use App\Jobs\SendTicketAssignedEmail;
use App\Jobs\SendTicketStatusEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class TicketService
{
    public function __construct(
        protected TicketRepository $ticketRepository,
        protected FileUploadService $fileUploadService
    ) {}

    /**
     * Get filtered tickets
     */
    public function getFiltered(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->ticketRepository->getFiltered($filters, $perPage);
    }

    /**
     * Get tickets for a specific user
     */
    public function getForUser(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->ticketRepository->getForUser($user, $filters, $perPage);
    }

    /**
     * Get ticket with all relations
     */
    public function getWithRelations(int $id): ?Ticket
    {
        return $this->ticketRepository->getWithRelations($id);
    }

    /**
     * Create a new ticket with transaction
     */
    public function create(array $data, ?array $files = null): Ticket
    {
        return DB::transaction(function () use ($data, $files) {
            // Create the ticket
            $ticket = $this->ticketRepository->create([
                'title' => $data['title'],
                'description' => $data['description'],
                'priority' => $data['priority'],
                'category_id' => $data['category_id'] ?? null,
                'created_by' => auth()->id(),
                'due_date' => $data['due_date'] ?? null,
                'estimated_hours' => $data['estimated_hours'] ?? null,
            ]);

            // Handle assignment if provided
            if (!empty($data['assigned_to'])) {
                $this->assignTicket($ticket, $data['assigned_to']);
            }

            // Handle file uploads
            if ($files) {
                $this->handleFileUploads($ticket, $files);
            }

            // Log activity
            TicketActivity::log($ticket, 'created', null, null, null, 
                "Ticket {$ticket->ticket_number} was created");

            // Dispatch event for real-time notification
            event(new TicketCreated($ticket));

            // Clear cache
            $this->ticketRepository->clearStatisticsCache();

            Log::info('Ticket created', ['ticket_id' => $ticket->id, 'user_id' => auth()->id()]);

            return $ticket->fresh(['creator', 'assignee', 'category']);
        });
    }

    /**
     * Update a ticket with transaction
     */
    public function update(Ticket $ticket, array $data, ?array $files = null): Ticket
    {
        return DB::transaction(function () use ($ticket, $data, $files) {
            $originalData = $ticket->toArray();

            // Update ticket fields
            $ticket->update([
                'title' => $data['title'] ?? $ticket->title,
                'description' => $data['description'] ?? $ticket->description,
                'priority' => $data['priority'] ?? $ticket->priority,
                'category_id' => $data['category_id'] ?? $ticket->category_id,
                'due_date' => $data['due_date'] ?? $ticket->due_date,
                'estimated_hours' => $data['estimated_hours'] ?? $ticket->estimated_hours,
            ]);

            // Handle assignment change
            if (isset($data['assigned_to']) && $data['assigned_to'] != $ticket->assigned_to) {
                $this->assignTicket($ticket, $data['assigned_to']);
            }

            // Handle file uploads
            if ($files) {
                $this->handleFileUploads($ticket, $files);
            }

            // Log activity
            TicketActivity::log($ticket, 'updated', null, 
                json_encode($originalData), json_encode($ticket->toArray()),
                "Ticket {$ticket->ticket_number} was updated");

            // Clear cache
            $this->ticketRepository->clearStatisticsCache();
            if ($ticket->assigned_to) {
                $this->ticketRepository->clearStatisticsCache($ticket->assigned_to);
            }

            return $ticket->fresh(['creator', 'assignee', 'category']);
        });
    }

    /**
     * Assign ticket to a user
     */
    public function assignTicket(Ticket $ticket, int $userId): Ticket
    {
        $oldAssignee = $ticket->assigned_to;
        
        $ticket->update([
            'assigned_to' => $userId,
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'status' => TicketStatus::ASSIGNED,
        ]);

        // Log activity
        TicketActivity::log($ticket, 'assigned', 'assigned_to', 
            $oldAssignee ? User::find($oldAssignee)?->name : null,
            User::find($userId)?->name,
            "Ticket assigned to " . User::find($userId)?->name);

        // Dispatch real-time event
        event(new TicketAssigned($ticket, User::find($userId)));

        // Queue email notification
        SendTicketAssignedEmail::dispatch($ticket, User::find($userId));

        // Clear cache
        $this->ticketRepository->clearStatisticsCache($userId);
        if ($oldAssignee) {
            $this->ticketRepository->clearStatisticsCache($oldAssignee);
        }

        return $ticket;
    }

    /**
     * Update ticket status (AJAX for developers)
     */
    public function updateStatus(Ticket $ticket, string $status): array
    {
        $newStatus = TicketStatus::from($status);
        
        // Validate status transition
        if (!$ticket->status->canTransitionTo($newStatus)) {
            return [
                'success' => false,
                'message' => "Cannot change status from {$ticket->status->label()} to {$newStatus->label()}",
            ];
        }

        $oldStatus = $ticket->status;

        DB::transaction(function () use ($ticket, $newStatus, $oldStatus) {
            $updateData = ['status' => $newStatus];

            // Set timestamps based on new status
            if ($newStatus === TicketStatus::IN_PROCESS && !$ticket->started_at) {
                $updateData['started_at'] = now();
            } elseif ($newStatus === TicketStatus::COMPLETED) {
                $updateData['completed_at'] = now();
            }

            $ticket->update($updateData);

            // Log activity
            TicketActivity::log($ticket, 'status_changed', 'status',
                $oldStatus->label(), $newStatus->label(),
                "Status changed from {$oldStatus->label()} to {$newStatus->label()}");

            // Dispatch real-time event
            event(new TicketStatusChanged($ticket, $oldStatus, $newStatus));

            // Queue email notification
            SendTicketStatusEmail::dispatch($ticket, $oldStatus, $newStatus);

            // Clear cache
            $this->ticketRepository->clearStatisticsCache();
            if ($ticket->assigned_to) {
                $this->ticketRepository->clearStatisticsCache($ticket->assigned_to);
            }
        });

        return [
            'success' => true,
            'message' => "Status updated to {$newStatus->label()}",
            'ticket' => $ticket->fresh(),
        ];
    }

    /**
     * Delete ticket (soft delete)
     */
    public function delete(Ticket $ticket): bool
    {
        return DB::transaction(function () use ($ticket) {
            TicketActivity::log($ticket, 'deleted', null, null, null,
                "Ticket {$ticket->ticket_number} was deleted");

            $result = $ticket->delete();

            // Clear cache
            $this->ticketRepository->clearStatisticsCache();
            if ($ticket->assigned_to) {
                $this->ticketRepository->clearStatisticsCache($ticket->assigned_to);
            }

            return $result;
        });
    }

    /**
     * Restore soft deleted ticket
     */
    public function restore(int $id): Ticket
    {
        return DB::transaction(function () use ($id) {
            $ticket = Ticket::withTrashed()->findOrFail($id);
            $ticket->restore();

            TicketActivity::log($ticket, 'restored', null, null, null,
                "Ticket {$ticket->ticket_number} was restored");

            // Clear cache
            $this->ticketRepository->clearStatisticsCache();

            return $ticket;
        });
    }

    /**
     * Get ticket statistics
     */
    public function getStatistics(?int $userId = null): array
    {
        return $this->ticketRepository->getStatistics($userId);
    }

    /**
     * Handle file uploads for a ticket
     */
    protected function handleFileUploads(Ticket $ticket, array $files): void
    {
        foreach ($files as $file) {
            $this->fileUploadService->upload($file, $ticket);
        }
    }
}

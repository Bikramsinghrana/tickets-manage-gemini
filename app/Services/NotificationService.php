<?php

namespace App\Services;

use App\Models\User;
use App\Models\Ticket;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\TicketStatusChangedNotification;
use App\Notifications\TicketCommentNotification;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Send ticket assigned notification
     */
    public function sendTicketAssigned(Ticket $ticket, User $assignee): void
    {
        $assignee->notify(new TicketAssignedNotification($ticket));
    }

    /**
     * Send ticket status changed notification
     */
    public function sendStatusChanged(Ticket $ticket, string $oldStatus, string $newStatus): void
    {
        // Notify ticket creator
        if ($ticket->creator && $ticket->creator->id !== auth()->id()) {
            $ticket->creator->notify(
                new TicketStatusChangedNotification($ticket, $oldStatus, $newStatus)
            );
        }

        // Notify managers if completed
        if ($newStatus === 'completed') {
            $managers = User::role('manager')->get();
            Notification::send($managers, new TicketStatusChangedNotification($ticket, $oldStatus, $newStatus));
        }
    }

    /**
     * Send new comment notification
     */
    public function sendNewComment(Ticket $ticket, string $comment): void
    {
        $commenter = auth()->user();
        $recipients = collect();

        // Do not notify the commenter themselves; only notify other parties

        // Add ticket creator
        if ($ticket->creator && $ticket->creator->id !== $commenter?->id) {
            $recipients->push($ticket->creator);
        }

        // Add assignee
        if ($ticket->assignee && $ticket->assignee->id !== $commenter?->id) {
            $recipients->push($ticket->assignee);
        }

        // // Add admin and manager users so they receive ticket updates
        // $staffRecipients = User::whereHas('roles', function ($query) {
        //     $query->whereIn('name', ['admin', 'manager']);
        // })->get();

        // foreach ($staffRecipients as $recipient) {
        //     if ($recipient->id !== $commenter?->id) {
        //         $recipients->push($recipient);
        //     }
        // }

        $recipients = $recipients->unique('id');

        Notification::send(
            $recipients,
            new TicketCommentNotification(
                $ticket,
                $comment,
                $commenter?->id,
                $commenter?->name ?? 'Someone'
            )
        );
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(User $user, string $notificationId): bool
    {
        $notification = $user->notifications()->find($notificationId);
        
        if ($notification) {
            $notification->markAsRead();
            return true;
        }
        
        return false;
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(User $user): int
    {
        return $user->unreadNotifications()->update(['read_at' => now()]);
    }

    /**
     * Get unread notification count
     */
    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    /**
     * Get paginated notifications
     */
    public function getPaginated(User $user, ?int $perPage = null)
    {
        $perPage ??= (int) config('services.pagination.per_page', 10);

        return $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}

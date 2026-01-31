<?php

namespace App\Events;

use App\Models\Ticket;
use App\Enums\TicketStatus;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Ticket $ticket,
        public TicketStatus $oldStatus,
        public TicketStatus $newStatus
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [
            new Channel('tickets'),
        ];

        // Notify the ticket creator
        if ($this->ticket->created_by) {
            $channels[] = new PrivateChannel('user.' . $this->ticket->created_by);
        }

        // Notify the assigner (manager)
        if ($this->ticket->assigned_by && $this->ticket->assigned_by !== $this->ticket->created_by) {
            $channels[] = new PrivateChannel('user.' . $this->ticket->assigned_by);
        }

        return $channels;
    }

    /**
     * Get the broadcast event name.
     */
    public function broadcastAs(): string
    {
        return 'ticket.status_changed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => $this->ticket->title,
            'old_status' => $this->oldStatus->value,
            'old_status_label' => $this->oldStatus->label(),
            'new_status' => $this->newStatus->value,
            'new_status_label' => $this->newStatus->label(),
            'new_status_color' => $this->newStatus->color(),
            'assignee' => $this->ticket->assignee ? [
                'id' => $this->ticket->assignee->id,
                'name' => $this->ticket->assignee->name,
            ] : null,
            'updated_by' => auth()->user() ? [
                'id' => auth()->id(),
                'name' => auth()->user()->name,
            ] : null,
            'updated_at' => now()->toISOString(),
            'message' => "Ticket {$this->ticket->ticket_number} status changed from {$this->oldStatus->label()} to {$this->newStatus->label()}",
        ];
    }
}

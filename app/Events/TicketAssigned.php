<?php

namespace App\Events;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Ticket $ticket,
        public User $assignee
    ) {}

    /**
     * Get the channels the event should broadcast on.
     * Private channel for the specific user
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->assignee->id),
            new Channel('tickets'),
        ];
    }

    /**
     * Get the broadcast event name.
     */
    public function broadcastAs(): string
    {
        return 'ticket.assigned';
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
            'priority' => $this->ticket->priority->value,
            'priority_label' => $this->ticket->priority->label(),
            'priority_color' => $this->ticket->priority->color(),
            'status' => $this->ticket->status->value,
            'status_label' => $this->ticket->status->label(),
            'assignee' => [
                'id' => $this->assignee->id,
                'name' => $this->assignee->name,
            ],
            'assigner' => $this->ticket->assigner ? [
                'id' => $this->ticket->assigner->id,
                'name' => $this->ticket->assigner->name,
            ] : null,
            'category' => $this->ticket->category ? [
                'id' => $this->ticket->category->id,
                'name' => $this->ticket->category->name,
            ] : null,
            'due_date' => $this->ticket->due_date?->toISOString(),
            'assigned_at' => $this->ticket->assigned_at->toISOString(),
            'message' => "You have been assigned to ticket {$this->ticket->ticket_number}: {$this->ticket->title}",
        ];
    }
}

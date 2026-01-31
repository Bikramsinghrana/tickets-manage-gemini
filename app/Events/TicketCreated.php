<?php

namespace App\Events;

use App\Models\Ticket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Ticket $ticket
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('tickets'),
        ];
    }

    /**
     * Get the broadcast event name.
     */
    public function broadcastAs(): string
    {
        return 'ticket.created';
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
            'creator' => [
                'id' => $this->ticket->creator->id,
                'name' => $this->ticket->creator->name,
            ],
            'category' => $this->ticket->category ? [
                'id' => $this->ticket->category->id,
                'name' => $this->ticket->category->name,
                'color' => $this->ticket->category->color,
            ] : null,
            'created_at' => $this->ticket->created_at->toISOString(),
        ];
    }
}

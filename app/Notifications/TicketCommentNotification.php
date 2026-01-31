<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TicketCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Ticket $ticket,
        public string $comment
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => $this->ticket->title,
            'comment_preview' => substr($this->comment, 0, 100) . (strlen($this->comment) > 100 ? '...' : ''),
            'commenter_name' => auth()->user()?->name ?? 'Someone',
            'message' => "New comment on ticket {$this->ticket->ticket_number}",
            'url' => route('tickets.show', $this->ticket) . '#comments',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'new_comment',
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'comment_preview' => substr($this->comment, 0, 100),
            'commenter_name' => auth()->user()?->name ?? 'Someone',
            'message' => "New comment on {$this->ticket->ticket_number}",
            'url' => route('tickets.show', $this->ticket) . '#comments',
            'created_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get the type of the notification being broadcast.
     */
    public function broadcastType(): string
    {
        return 'ticket.commented';
    }
}

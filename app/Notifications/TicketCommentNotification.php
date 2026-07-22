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
        public string $comment,
        public ?int $commenterId = null,
        public string $commenterName = 'Someone'
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Build a consistent absolute URL for the ticket comments section.
     */
    protected function buildTicketUrl(): string
    {
        $baseUrl = rtrim(config('app.url', url('/')), '/');

        // return $baseUrl . '/tickets/' . $this->ticket->id . '#comments';
        return route('tickets.show', $this->ticket->id);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $isSelf = $this->commenterId !== null && $notifiable->id === $this->commenterId;

        $message = $isSelf
            ? "You commented on ticket {$this->ticket->ticket_number}"
            : "{$this->commenterName} commented on ticket {$this->ticket->ticket_number}";

        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => $this->ticket->title,
            'comment_preview' => substr($this->comment, 0, 100) . (strlen($this->comment) > 100 ? '...' : ''),
            'commenter_name' => $this->commenterName,
            'message' => $message,
            'url' => $this->buildTicketUrl(),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $isSelf = $this->commenterId !== null && $notifiable->id === $this->commenterId;
        $message = $isSelf
            ? "You commented on ticket {$this->ticket->ticket_number}"
            : "{$this->commenterName} commented on ticket {$this->ticket->ticket_number}";

        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'new_comment',
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'comment_preview' => substr($this->comment, 0, 100),
            'commenter_name' => $this->commenterName,
            'message' => $message,
            'url' => $this->buildTicketUrl(),
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

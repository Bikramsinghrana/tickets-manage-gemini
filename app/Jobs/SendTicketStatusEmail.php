<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Enums\TicketStatus;
use App\Mail\TicketStatusChangedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendTicketStatusEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Ticket $ticket,
        public TicketStatus $oldStatus,
        public TicketStatus $newStatus
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $recipients = collect();

            // Notify ticket creator
            if ($this->ticket->creator) {
                $recipients->push($this->ticket->creator);
            }

            // Notify assigner (manager who assigned)
            if ($this->ticket->assigner && $this->ticket->assigner->id !== $this->ticket->creator?->id) {
                $recipients->push($this->ticket->assigner);
            }

            $recipients = $recipients->unique('id');

            foreach ($recipients as $recipient) {
                Mail::to($recipient->email)
                    ->send(new TicketStatusChangedMail(
                        $this->ticket, 
                        $this->oldStatus, 
                        $this->newStatus,
                        $recipient
                    ));
            }

            Log::info('Ticket status email sent', [
                'ticket_id' => $this->ticket->id,
                'old_status' => $this->oldStatus->value,
                'new_status' => $this->newStatus->value,
                'recipients' => $recipients->pluck('id')->toArray(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send ticket status email', [
                'ticket_id' => $this->ticket->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Ticket status email job failed permanently', [
            'ticket_id' => $this->ticket->id,
            'error' => $exception->getMessage(),
        ]);
    }
}

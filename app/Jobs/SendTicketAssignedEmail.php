<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Models\User;
use App\Mail\TicketAssignedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendTicketAssignedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Ticket $ticket,
        public User $assignee
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->assignee->email)
                ->send(new TicketAssignedMail($this->ticket, $this->assignee));

            Log::info('Ticket assigned email sent', [
                'ticket_id' => $this->ticket->id,
                'assignee_id' => $this->assignee->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send ticket assigned email', [
                'ticket_id' => $this->ticket->id,
                'assignee_id' => $this->assignee->id,
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
        Log::error('Ticket assigned email job failed permanently', [
            'ticket_id' => $this->ticket->id,
            'assignee_id' => $this->assignee->id,
            'error' => $exception->getMessage(),
        ]);
    }
}

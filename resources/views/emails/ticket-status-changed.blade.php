<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #6366f1; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
        .ticket-info { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .btn { display: inline-block; background: #6366f1; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; }
        .status-change { display: flex; align-items: center; justify-content: center; gap: 10px; margin: 20px 0; }
        .status { padding: 8px 16px; border-radius: 6px; font-weight: bold; }
        .arrow { font-size: 24px; color: #6366f1; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Ticket Status Updated</h1>
        </div>
        <div class="content">
            <p>Hello {{ $recipient->name }},</p>
            <p>The status of ticket <strong>{{ $ticket->ticket_number }}</strong> has been updated:</p>
            
            <div class="status-change">
                <span class="status" style="background: #e2e8f0;">{{ $oldStatus->label() }}</span>
                <span class="arrow">→</span>
                <span class="status" style="background: #d1fae5; color: #065f46;">{{ $newStatus->label() }}</span>
            </div>
            
            <div class="ticket-info">
                <h3 style="margin-top: 0;">{{ $ticket->title }}</h3>
                @if($ticket->assignee)
                <p><strong>Assigned To:</strong> {{ $ticket->assignee->name }}</p>
                @endif
                @if($newStatus->value === 'completed')
                <p><strong>Completed At:</strong> {{ now()->format('F d, Y h:i A') }}</p>
                @endif
            </div>
            
            <p style="text-align: center;">
                <a href="{{ $ticketUrl }}" class="btn">View Ticket</a>
            </p>
            
            <p>Best regards,<br>{{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>

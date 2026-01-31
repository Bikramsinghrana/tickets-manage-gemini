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
        .priority { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .priority-low { background: #d1fae5; color: #065f46; }
        .priority-medium { background: #dbeafe; color: #1e40af; }
        .priority-high { background: #fef3c7; color: #92400e; }
        .priority-urgent { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Ticket Assigned</h1>
        </div>
        <div class="content">
            <p>Hello {{ $assignee->name }},</p>
            <p>A new ticket has been assigned to you:</p>
            
            <div class="ticket-info">
                <h2 style="margin-top: 0;">{{ $ticket->ticket_number }}</h2>
                <h3>{{ $ticket->title }}</h3>
                <p><strong>Priority:</strong> 
                    <span class="priority priority-{{ $ticket->priority->value }}">{{ $ticket->priority->label() }}</span>
                </p>
                @if($ticket->category)
                <p><strong>Category:</strong> {{ $ticket->category->name }}</p>
                @endif
                @if($ticket->due_date)
                <p><strong>Due Date:</strong> {{ $ticket->due_date->format('F d, Y') }}</p>
                @endif
                <p><strong>Description:</strong></p>
                <p>{{ Str::limit($ticket->description, 300) }}</p>
            </div>
            
            <p style="text-align: center;">
                <a href="{{ $ticketUrl }}" class="btn">View Ticket</a>
            </p>
            
            <p>Best regards,<br>{{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>

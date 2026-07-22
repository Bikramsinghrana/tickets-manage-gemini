@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Notification</h5>
            <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-link">Back</a>
        </div>
        <div class="card-body">
            <p class="mb-2"><strong>{{ $notification->data['message'] ?? 'Notification' }}</strong></p>
            <p class="text-muted small mb-3">{{ $notification->created_at->diffForHumans() }}</p>

            @if(isset($notification->data['ticket_number']))
                <p>Ticket: <a href="{{ $notification->data['url'] ?? '#' }}">{{ $notification->data['ticket_number'] }}</a></p>
            @endif

            @if(isset($notification->data['comment_preview']))
                <div class="border rounded p-3 bg-light">
                    <p class="mb-0">{{ $notification->data['comment_preview'] }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

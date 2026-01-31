@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-bell me-2"></i>All Notifications</h6>
        <button class="btn btn-sm btn-outline-primary" id="markAllReadPage">Mark All as Read</button>
    </div>
    <div class="card-body p-0">
        @forelse($notifications as $notification)
        <div class="p-3 border-bottom {{ $notification->read_at ? '' : 'bg-light' }}">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="mb-1 {{ $notification->read_at ? 'text-muted' : 'fw-semibold' }}">
                        {{ $notification->data['message'] ?? 'New notification' }}
                    </p>
                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                </div>
                @if(isset($notification->data['url']))
                <a href="{{ $notification->data['url'] }}" class="btn btn-sm btn-outline-primary">View</a>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="fas fa-bell-slash fa-3x mb-3"></i>
            <p>No notifications</p>
        </div>
        @endforelse
    </div>
    @if($notifications->hasPages())
    <div class="card-footer">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$('#markAllReadPage').on('click', function() {
    $.post('{{ route('notifications.mark-all-read') }}').done(function() {
        location.reload();
    });
});
</script>
@endpush

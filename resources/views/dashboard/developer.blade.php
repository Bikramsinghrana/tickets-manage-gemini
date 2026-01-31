@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'My Dashboard')

@section('content')
<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card primary">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="mb-1 opacity-75">My Tickets</p>
                    <h2 class="mb-0 fw-bold">{{ number_format($statistics['total']) }}</h2>
                </div>
                <div class="opacity-50">
                    <i class="fas fa-ticket-alt fa-3x"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="stat-card info">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="mb-1 opacity-75">Assigned</p>
                    <h2 class="mb-0 fw-bold">{{ number_format($statistics['assigned']) }}</h2>
                </div>
                <div class="opacity-50">
                    <i class="fas fa-user-check fa-3x"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="stat-card warning">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="mb-1 opacity-75">In Progress</p>
                    <h2 class="mb-0 fw-bold">{{ number_format($statistics['in_process']) }}</h2>
                </div>
                <div class="opacity-50">
                    <i class="fas fa-spinner fa-3x"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="stat-card success">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="mb-1 opacity-75">Completed</p>
                    <h2 class="mb-0 fw-bold">{{ number_format($statistics['completed']) }}</h2>
                </div>
                <div class="opacity-50">
                    <i class="fas fa-check-circle fa-3x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Assigned Tickets -->
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center bg-info bg-opacity-10">
                <h6 class="mb-0 text-info">
                    <i class="fas fa-user-check me-2"></i>Assigned to Me
                </h6>
                <span class="badge bg-info">{{ $assignedTickets->total() }}</span>
            </div>
            <div class="card-body p-0">
                @forelse($assignedTickets as $ticket)
                <div class="p-3 border-bottom hover-bg-light">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <a href="{{ route('tickets.show', $ticket) }}" class="text-decoration-none">
                                <span class="fw-semibold text-primary">{{ $ticket->ticket_number }}</span>
                            </a>
                            <h6 class="mb-1 mt-1">{{ Str::limit($ticket->title, 50) }}</h6>
                            <div class="d-flex gap-2 mt-2">
                                <span class="{{ $ticket->priority->badgeClass() }} small">
                                    <i class="fas {{ $ticket->priority->icon() }} me-1"></i>
                                    {{ $ticket->priority->label() }}
                                </span>
                                @if($ticket->category)
                                <span class="badge bg-light text-dark small">
                                    {{ $ticket->category->name }}
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="text-end">
                            <button class="btn btn-sm btn-outline-primary start-work-btn" 
                                    data-ticket-id="{{ $ticket->id }}">
                                <i class="fas fa-play me-1"></i>Start
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <p class="mb-0">No assigned tickets</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    
    <!-- In Progress Tickets -->
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center bg-warning bg-opacity-10">
                <h6 class="mb-0 text-warning">
                    <i class="fas fa-spinner me-2"></i>In Progress
                </h6>
                <span class="badge bg-warning text-dark">{{ $inProcessTickets->total() }}</span>
            </div>
            <div class="card-body p-0">
                @forelse($inProcessTickets as $ticket)
                <div class="p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <a href="{{ route('tickets.show', $ticket) }}" class="text-decoration-none">
                                <span class="fw-semibold text-primary">{{ $ticket->ticket_number }}</span>
                            </a>
                            <h6 class="mb-1 mt-1">{{ Str::limit($ticket->title, 50) }}</h6>
                            <div class="d-flex gap-2 mt-2">
                                <span class="{{ $ticket->priority->badgeClass() }} small">
                                    <i class="fas {{ $ticket->priority->icon() }} me-1"></i>
                                    {{ $ticket->priority->label() }}
                                </span>
                                @if($ticket->due_date)
                                <span class="badge {{ $ticket->is_overdue ? 'bg-danger' : 'bg-light text-dark' }} small">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $ticket->due_date->diffForHumans() }}
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="text-end">
                            <button class="btn btn-sm btn-success complete-btn" 
                                    data-ticket-id="{{ $ticket->id }}">
                                <i class="fas fa-check me-1"></i>Complete
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-tasks fa-2x mb-2"></i>
                    <p class="mb-0">No tickets in progress</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Recent Tickets</h6>
                <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Ticket</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Updated</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTickets as $ticket)
                            <tr>
                                <td>
                                    <a href="{{ route('tickets.show', $ticket) }}" class="fw-semibold text-primary text-decoration-none">
                                        {{ $ticket->ticket_number }}
                                    </a>
                                </td>
                                <td>{{ Str::limit($ticket->title, 40) }}</td>
                                <td>
                                    <span class="{{ $ticket->status->badgeClass() }}">
                                        {{ $ticket->status->label() }}
                                    </span>
                                </td>
                                <td>
                                    <span class="{{ $ticket->priority->badgeClass() }}">
                                        <i class="fas {{ $ticket->priority->icon() }} me-1"></i>
                                        {{ $ticket->priority->label() }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $ticket->updated_at->diffForHumans() }}</td>
                                <td>
                                    <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No tickets found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Start work on ticket (AJAX)
    $('.start-work-btn').on('click', function() {
        const ticketId = $(this).data('ticket-id');
        const $btn = $(this);
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: `/tickets/${ticketId}/status`,
            method: 'PATCH',
            data: { status: 'in_process' },
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                }
            },
            error: function(xhr) {
                showToast(xhr.responseJSON?.message || 'Error updating status', 'danger');
                $btn.prop('disabled', false).html('<i class="fas fa-play me-1"></i>Start');
            }
        });
    });
    
    // Complete ticket (AJAX)
    $('.complete-btn').on('click', function() {
        const ticketId = $(this).data('ticket-id');
        const $btn = $(this);
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: `/tickets/${ticketId}/status`,
            method: 'PATCH',
            data: { status: 'completed' },
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                }
            },
            error: function(xhr) {
                showToast(xhr.responseJSON?.message || 'Error updating status', 'danger');
                $btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i>Complete');
            }
        });
    });
    
    // Real-time notifications
    @if(config('broadcasting.default') === 'pusher')
    const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
        cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
        encrypted: true
    });
    
    const userChannel = pusher.subscribe('private-user.{{ auth()->id() }}');
    
    userChannel.bind('ticket.assigned', function(data) {
        showToast(data.message, 'info');
        // Refresh after a short delay
        setTimeout(() => location.reload(), 3000);
    });
    @endif
</script>
@endpush

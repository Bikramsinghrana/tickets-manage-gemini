@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card primary">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="mb-1 opacity-75">Total Tickets</p>
                    <h2 class="mb-0 fw-bold">{{ number_format($statistics['total']) }}</h2>
                </div>
                <div class="opacity-50"><i class="fas fa-ticket-alt fa-3x"></i></div>
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
                <div class="opacity-50"><i class="fas fa-user-check fa-3x"></i></div>
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
                <div class="opacity-50"><i class="fas fa-spinner fa-3x"></i></div>
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
                <div class="opacity-50"><i class="fas fa-check-circle fa-3x"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                    <i class="fas fa-clock text-danger fa-lg"></i>
                </div>
                <div>
                    <p class="mb-0 text-muted small">Overdue</p>
                    <h4 class="mb-0 fw-bold">{{ $statistics['overdue'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                    <i class="fas fa-exclamation-triangle text-warning fa-lg"></i>
                </div>
                <div>
                    <p class="mb-0 text-muted small">High Priority</p>
                    <h4 class="mb-0 fw-bold">{{ $statistics['high_priority'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                    <i class="fas fa-chart-line text-success fa-lg"></i>
                </div>
                <div>
                    <p class="mb-0 text-muted small">Completion Rate</p>
                    <h4 class="mb-0 fw-bold">{{ $statistics['completion_rate'] }}%</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card h-100">
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
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Assignee</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTickets as $ticket)
                            <tr>
                                <td>
                                    <a href="{{ route('tickets.show', $ticket) }}" class="text-decoration-none">
                                        <strong class="text-primary">{{ $ticket->ticket_number }}</strong><br>
                                        <small class="text-muted">{{ Str::limit($ticket->title, 40) }}</small>
                                    </a>
                                </td>
                                <td><span class="{{ $ticket->status->badgeClass() }}">{{ $ticket->status->label() }}</span></td>
                                <td><span class="{{ $ticket->priority->badgeClass() }}"><i class="fas {{ $ticket->priority->icon() }} me-1"></i>{{ $ticket->priority->label() }}</span></td>
                                <td>{{ $ticket->assignee?->name ?? 'Unassigned' }}</td>
                                <td class="text-muted small">{{ $ticket->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">No tickets yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Development Team</h6></div>
            <div class="card-body">
                @forelse($developers as $developer)
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <img src="{{ $developer->avatar_url }}" class="rounded-circle me-3" style="width: 40px; height: 40px;">
                        <div>
                            <p class="mb-0 fw-semibold">{{ $developer->name }}</p>
                            <small class="text-muted">{{ $developer->department ?? 'Developer' }}</small>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted">No developers found</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@can('manage-tickets')
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h6 class="mb-3">Quick Actions</h6>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('tickets.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Create Ticket</a>
                    @can('manage-categories')
                    <a href="{{ route('categories.index') }}" class="btn btn-outline-primary"><i class="fas fa-folder me-2"></i>Manage Categories</a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endcan
@endsection

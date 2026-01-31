@extends('layouts.app')

@section('title', 'Tickets')
@section('page-title', 'Ticket Management')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('tickets.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search tickets..." value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" {{ ($filters['status'] ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="priority" class="form-select">
                    <option value="">All Priority</option>
                    @foreach($priorities as $value => $label)
                    <option value="{{ $value }}" {{ ($filters['priority'] ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search me-2"></i>Filter</button>
                <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">Reset</a>
                @can('manage-tickets')
                <a href="{{ route('tickets.create') }}" class="btn btn-success"><i class="fas fa-plus me-2"></i>New</a>
                @endcan
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Assignee</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td><a href="{{ route('tickets.show', $ticket) }}" class="fw-bold text-primary text-decoration-none">{{ $ticket->ticket_number }}</a></td>
                        <td>{{ Str::limit($ticket->title, 35) }}</td>
                        <td>
                            @if($ticket->category)
                            <span class="badge" style="background-color: {{ $ticket->category->color }}20; color: {{ $ticket->category->color }}">{{ $ticket->category->name }}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td><span class="{{ $ticket->status->badgeClass() }}">{{ $ticket->status->label() }}</span></td>
                        <td><span class="{{ $ticket->priority->badgeClass() }}"><i class="fas {{ $ticket->priority->icon() }} me-1"></i>{{ $ticket->priority->label() }}</span></td>
                        <td>{{ $ticket->assignee?->name ?? 'Unassigned' }}</td>
                        <td class="text-muted small">{{ $ticket->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('tickets.show', $ticket) }}"><i class="fas fa-eye me-2"></i>View</a></li>
                                    @can('update', $ticket)
                                    <li><a class="dropdown-item" href="{{ route('tickets.edit', $ticket) }}"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                    @endcan
                                    @can('delete', $ticket)
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('tickets.destroy', $ticket) }}" method="POST" onsubmit="return confirm('Delete this ticket?')">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item text-danger"><i class="fas fa-trash me-2"></i>Delete</button>
                                        </form>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-ticket-alt fa-3x mb-3"></i><p>No tickets found</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($tickets->hasPages())
    <div class="card-footer">{{ $tickets->links() }}</div>
    @endif
</div>
@endsection

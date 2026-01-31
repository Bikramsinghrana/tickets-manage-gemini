@extends('layouts.app')

@section('title', 'User Dashboard')

@section('content')

<h4 class="mb-3">My Tickets</h4>

<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Description</th>
            <th>Status</th>
            <th>Update</th>
        </tr>
    </thead>
    <tbody>
        @forelse($myTickets as $ticket)
            <tr>
                <td>{{ $ticket->id }}</td>
                <td>{{ $ticket->title }}</td>
                <td>{{ $ticket->description }}</td>
                <td>
                    @include('components.status-badge', ['status' => $ticket->status])
                </td>
                <td>
                    <form method="POST" action="{{ route('tickets.update', $ticket->id) }}">
                        @csrf
                        @method('PUT')
                        <select name="status" class="form-select form-select-sm"
                                onchange="this.form.submit()">
                            <option value="open" {{ $ticket->status->value==='open'?'selected':'' }}>Open</option>
                            <option value="in_progress" {{ $ticket->status->value==='in_progress'?'selected':'' }}>In Progress</option>
                            <option value="resolved" {{ $ticket->status->value==='resolved'?'selected':'' }}>Resolved</option>
                            <option value="closed" {{ $ticket->status->value==='closed'?'selected':'' }}>Closed</option>
                        </select>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">No assigned tickets</td>
            </tr>
        @endforelse
    </tbody>
</table>

{{ $myTickets->links() }}

@endsection

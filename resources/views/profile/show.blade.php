@extends('layouts.app')

@section('title', 'Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="row">
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-body text-center py-5">
                <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="rounded-circle mb-4" style="width: 120px; height: 120px;">
                <h4 class="fw-bold">{{ auth()->user()->name }}</h4>
                <p class="text-muted">{{ auth()->user()->email }}</p>
                <span class="{{ auth()->user()->primary_role_enum?->badgeClass() ?? 'badge bg-secondary' }}">
                    {{ auth()->user()->primary_role_enum?->label() ?? 'User' }}
                </span>
                @if(auth()->user()->department)
                <p class="mt-3 mb-0 text-muted"><i class="fas fa-building me-2"></i>{{ auth()->user()->department }}</p>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-xl-8">
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Profile Information</h6></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3 text-muted">Full Name</dt>
                    <dd class="col-sm-9">{{ auth()->user()->name }}</dd>
                    
                    <dt class="col-sm-3 text-muted">Email</dt>
                    <dd class="col-sm-9">{{ auth()->user()->email }}</dd>
                    
                    <dt class="col-sm-3 text-muted">Phone</dt>
                    <dd class="col-sm-9">{{ auth()->user()->phone ?? 'Not set' }}</dd>
                    
                    <dt class="col-sm-3 text-muted">Department</dt>
                    <dd class="col-sm-9">{{ auth()->user()->department ?? 'Not set' }}</dd>
                    
                    <dt class="col-sm-3 text-muted">Role</dt>
                    <dd class="col-sm-9">{{ auth()->user()->primary_role_enum?->label() ?? 'User' }}</dd>
                    
                    <dt class="col-sm-3 text-muted">Member Since</dt>
                    <dd class="col-sm-9">{{ auth()->user()->created_at->format('F d, Y') }}</dd>
                    
                    <dt class="col-sm-3 text-muted">Last Login</dt>
                    <dd class="col-sm-9">{{ auth()->user()->last_login_at?->diffForHumans() ?? 'Never' }}</dd>
                </dl>
            </div>
        </div>
        
        @if(auth()->user()->bio)
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Bio</h6></div>
            <div class="card-body"><p class="mb-0">{{ auth()->user()->bio }}</p></div>
        </div>
        @endif
        
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Ticket Statistics</h6></div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h3 class="fw-bold text-primary mb-0">{{ auth()->user()->assignedTickets()->count() }}</h3>
                            <small class="text-muted">Total Assigned</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h3 class="fw-bold text-info mb-0">{{ auth()->user()->assignedTickets()->where('status', 'assigned')->count() }}</h3>
                            <small class="text-muted">Assigned</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h3 class="fw-bold text-warning mb-0">{{ auth()->user()->assignedTickets()->where('status', 'in_process')->count() }}</h3>
                            <small class="text-muted">In Progress</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h3 class="fw-bold text-success mb-0">{{ auth()->user()->assignedTickets()->where('status', 'completed')->count() }}</h3>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

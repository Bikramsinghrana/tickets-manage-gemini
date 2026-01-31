<div class="sidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <h4><i class="fas fa-ticket-alt me-2"></i>TicketPro</h4>
    </div>
    
    <!-- User Info -->
    <div class="px-4 py-3 border-bottom border-secondary border-opacity-25">
        <div class="d-flex align-items-center">
            <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" 
                 class="rounded-circle me-3" style="width: 45px; height: 45px; object-fit: cover;">
            <div>
                <div class="text-white fw-semibold">{{ auth()->user()->name }}</div>
                <small class="text-white-50">
                    <span class="badge bg-{{ auth()->user()->primary_role_enum?->color() ?? 'secondary' }} bg-opacity-75">
                        {{ auth()->user()->primary_role_enum?->label() ?? 'User' }}
                    </span>
                </small>
            </div>
        </div>
    </div>
    
    <!-- Menu -->
    <nav class="sidebar-menu">
        <a href="{{ route('dashboard') }}" class="sidebar-menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-th-large"></i>
            <span>Dashboard</span>
        </a>
        
        <div class="sidebar-section-title">Tickets</div>
        
        <a href="{{ route('tickets.index') }}" class="sidebar-menu-item {{ request()->routeIs('tickets.index') ? 'active' : '' }}">
            <i class="fas fa-ticket-alt"></i>
            <span>All Tickets</span>
        </a>
        
        @can('manage-tickets')
        <a href="{{ route('tickets.create') }}" class="sidebar-menu-item {{ request()->routeIs('tickets.create') ? 'active' : '' }}">
            <i class="fas fa-plus-circle"></i>
            <span>Create Ticket</span>
        </a>
        @endcan
        
        @can('manage-categories')
        <div class="sidebar-section-title">Management</div>
        
        <a href="{{ route('categories.index') }}" class="sidebar-menu-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <i class="fas fa-folder"></i>
            <span>Categories</span>
        </a>
        @endcan
        
        <div class="sidebar-section-title">Account</div>
        
        <a href="{{ route('profile.show') }}" class="sidebar-menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="fas fa-user-circle"></i>
            <span>Profile</span>
        </a>
        
        <a href="{{ route('notifications.index') }}" class="sidebar-menu-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <i class="fas fa-bell"></i>
            <span>Notifications</span>
            @if(auth()->user()->unread_notification_count > 0)
                <span class="badge bg-danger ms-auto">{{ auth()->user()->unread_notification_count }}</span>
            @endif
        </a>
        
        <form method="POST" action="{{ route('logout') }}" class="mt-auto">
            @csrf
            <button type="submit" class="sidebar-menu-item w-100 text-start border-0 bg-transparent">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </button>
        </form>
    </nav>
</div>

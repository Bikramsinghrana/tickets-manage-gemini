<nav class="top-navbar d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center">
        <button class="btn btn-link d-lg-none me-3" id="sidebarToggle">
            <i class="fas fa-bars fa-lg text-dark"></i>
        </button>
        <h5 class="mb-0 fw-semibold">@yield('page-title', 'Dashboard')</h5>
    </div>
    
    <div class="d-flex align-items-center gap-3">
        <!-- Search -->
        <div class="d-none d-md-block">
            <form action="{{ route('tickets.index') }}" method="GET" class="position-relative">
                <input type="text" name="search" class="form-control ps-4" 
                       placeholder="Search tickets..." style="width: 250px; border-radius: 20px;"
                       value="{{ request('search') }}">
                <i class="fas fa-search position-absolute" style="left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
            </form>
        </div>
        
        <!-- Notifications -->
        <div class="dropdown">
            <button class="btn btn-link notification-bell" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-bell fa-lg text-secondary"></i>
                <span class="notification-badge" id="notificationCount" style="{{ auth()->user()->unread_notification_count > 0 ? '' : 'display: none;' }}">
                    {{ auth()->user()->unread_notification_count }}
                </span>
            </button>
            <div class="dropdown-menu dropdown-menu-end notification-dropdown p-0">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">Notifications</h6>
                    <button class="btn btn-sm btn-link text-primary p-0" id="markAllRead">
                        Mark all as read
                    </button>
                </div>
                <div id="notificationList">
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-bell-slash fa-2x mb-2"></i>
                        <p class="mb-0">Loading notifications...</p>
                    </div>
                </div>
                <div class="p-2 border-top text-center">
                    <a href="{{ route('notifications.index') }}" class="text-primary text-decoration-none small">
                        View all notifications
                    </a>
                </div>
            </div>
        </div>
        
        <!-- User Dropdown -->
        <div class="dropdown">
            <button class="btn btn-link d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" 
                     class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover;">
                <span class="d-none d-md-inline text-dark">{{ auth()->user()->name }}</span>
                <i class="fas fa-chevron-down text-muted small"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('profile.show') }}">
                    <i class="fas fa-user me-2"></i>Profile
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

@push('scripts')
<script>
    // Load notifications on dropdown open
    $('.notification-bell').on('click', function() {
        loadNotifications();
    });
    
    function loadNotifications() {
        $.get('{{ route('notifications.recent') }}')
            .done(function(data) {
                renderNotifications(data.notifications);
                updateNotificationCount(data.unread_count);
            })
            .fail(function() {
                $('#notificationList').html('<div class="p-4 text-center text-muted">Failed to load notifications</div>');
            });
    }
    
    function renderNotifications(notifications) {
        if (notifications.length === 0) {
            $('#notificationList').html('<div class="p-4 text-center text-muted"><i class="fas fa-bell-slash fa-2x mb-2"></i><p class="mb-0">No notifications</p></div>');
            return;
        }
        
        let html = '';
        notifications.forEach(function(n) {
            html += `
                <a href="${n.url}" class="notification-item d-block text-decoration-none ${n.is_read ? '' : 'unread'}" 
                   data-id="${n.id}">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="mb-1 text-dark">${n.message}</p>
                            <small class="text-muted">${n.created_at}</small>
                        </div>
                        ${!n.is_read ? '<span class="ms-2"><i class="fas fa-circle text-primary" style="font-size: 8px;"></i></span>' : ''}
                    </div>
                </a>
            `;
        });
        
        $('#notificationList').html(html);
    }
    
    function updateNotificationCount(count) {
        const $badge = $('#notificationCount');
        if (count > 0) {
            $badge.text(count).show();
        } else {
            $badge.hide();
        }
    }
    
    // Mark all as read
    $('#markAllRead').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        $.post('{{ route('notifications.mark-all-read') }}')
            .done(function(data) {
                updateNotificationCount(0);
                $('#notificationList .notification-item').removeClass('unread');
                showToast('All notifications marked as read', 'success');
            });
    });
    
    // Mark single notification as read on click
    $(document).on('click', '.notification-item', function() {
        const id = $(this).data('id');
        if (id) {
            $.post(`/notifications/${id}/read`);
        }
    });
</script>
@endpush

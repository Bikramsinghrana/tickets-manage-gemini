// Real-time notifications using Pusher
(function() {
    // Check if Pusher is available and if user is authenticated
    if (typeof Pusher === 'undefined' || !document.body.dataset.userId) {
        return;
    }

    // Initialize Pusher
    Pusher.logToConsole = false;
    
    const pusher = new Pusher(document.body.dataset.pusherKey, {
        cluster: document.body.dataset.pusherCluster,
        encrypted: true
    });

    let notificationCount = 0;
    const notificationBell = document.getElementById('notificationBell');
    const notificationBadge = document.getElementById('notificationBadge');
    const toastContainer = document.getElementById('toastContainer');

    // Subscribe to public tickets channel
    const ticketsChannel = pusher.subscribe('tickets');

    // Handle ticket created event
    ticketsChannel.bind('ticket.created', function(data) {
        showNotification(
            'New Ticket',
            data.message,
            'info'
        );
        updateNotificationBell();
    });

    // Handle ticket status changed event
    ticketsChannel.bind('ticket.status-changed', function(data) {
        showNotification(
            'Status Updated',
            data.message,
            'warning'
        );
        updateNotificationBell();
    });

    // Subscribe to private user channel if logged in
    const userId = document.body.dataset.userId;
    if (userId) {
        const userChannel = pusher.subscribe('user.' + userId);

        // Handle ticket assigned event
        userChannel.bind('ticket.assigned', function(data) {
            showNotification(
                'Ticket Assigned',
                data.message,
                'success'
            );
            updateNotificationBell();
        });
    }

    /**
     * Show a toast notification
     */
    function showNotification(title, message, type = 'info') {
        if (!toastContainer) return;

        const toastId = 'toast-' + Date.now();
        const bgClass = 'bg-' + type;

        const toastHTML = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}</strong><br>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement);
        toast.show();

        // Remove toast from DOM after it's hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    }

    /**
     * Update notification bell with badge count
     */
    function updateNotificationBell() {
        notificationCount++;
        
        if (notificationBell && notificationBadge) {
            if (notificationCount > 0) {
                notificationBadge.classList.remove('d-none');
                notificationBadge.textContent = notificationCount > 99 ? '99+' : notificationCount;
                notificationBell.classList.add('animate-bell');
            }
        }
    }

    // Clear notification bell on click
    if (notificationBell) {
        notificationBell.addEventListener('click', function() {
            notificationCount = 0;
            notificationBadge.classList.add('d-none');
            notificationBell.classList.remove('animate-bell');
        });
    }
})();

// Add animation for notification bell
const style = document.createElement('style');
style.textContent = `
    @keyframes ring {
        0% { transform: rotate(0deg); }
        10% { transform: rotate(-10deg); }
        20% { transform: rotate(10deg); }
        30% { transform: rotate(-10deg); }
        40% { transform: rotate(10deg); }
        50% { transform: rotate(0deg); }
        100% { transform: rotate(0deg); }
    }
    
    .animate-bell {
        animation: ring 0.5s ease-in-out;
    }
`;
document.head.appendChild(style);

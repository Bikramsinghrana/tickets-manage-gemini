# TicketPro - System Architecture

## Overview

TicketPro is built on Laravel 10.10 following clean architecture principles with clear separation of concerns. The application implements the Repository Pattern for data access, Service Layer for business logic, and uses Spatie Laravel Permission for role-based access control.

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              PRESENTATION LAYER                              │
├─────────────────────────────────────────────────────────────────────────────┤
│  Blade Views         │  Bootstrap 5 + Tailwind CSS  │  JavaScript + jQuery  │
│  - layouts/          │  - Professional UI           │  - AJAX requests      │
│  - dashboard/        │  - Responsive design         │  - Pusher client      │
│  - tickets/          │  - Toast notifications       │  - Form handling      │
└─────────────────────────────────────────────────────────────────────────────┘
                                        │
                                        ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                              APPLICATION LAYER                               │
├─────────────────────────────────────────────────────────────────────────────┤
│  Controllers         │  Form Requests               │  Middleware           │
│  - TicketController  │  - TicketStoreRequest       │  - RoleMiddleware     │
│  - DashboardController│ - TicketStatusRequest      │  - CheckPermission    │
│  - NotificationController│ - CommentRequest        │  - Throttle           │
│  - CommentController │  - CategoryRequest          │                       │
└─────────────────────────────────────────────────────────────────────────────┘
                                        │
                                        ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                               SERVICE LAYER                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│  TicketService       │  FileUploadService          │  NotificationService  │
│  - create()          │  - upload()                 │  - sendTicketAssigned │
│  - update()          │  - uploadMultiple()         │  - sendStatusChanged  │
│  - assignTicket()    │  - delete()                 │  - markAsRead()       │
│  - updateStatus()    │  - download()               │  - markAllAsRead()    │
│  - delete/restore()  │                             │                       │
└─────────────────────────────────────────────────────────────────────────────┘
                                        │
                                        ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                              REPOSITORY LAYER                                │
├─────────────────────────────────────────────────────────────────────────────┤
│  TicketRepository    │  UserRepository             │  CategoryRepository   │
│  - getFiltered()     │  - getDevelopers()          │  - getForDropdown()   │
│  - getForUser()      │  - getAssignableUsers()     │  - getAllWithCounts() │
│  - getStatistics()   │  - getByRole()              │  - clearCache()       │
│  - getWithRelations()│  - clearCache()             │                       │
└─────────────────────────────────────────────────────────────────────────────┘
                                        │
                                        ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                                DATA LAYER                                    │
├─────────────────────────────────────────────────────────────────────────────┤
│  Eloquent Models     │  Spatie Permission          │  Database             │
│  - User (HasRoles)   │  - Role                     │  - users              │
│  - Ticket            │  - Permission               │  - tickets            │
│  - Category          │  - model_has_roles          │  - categories         │
│  - Comment           │  - role_has_permissions     │  - comments           │
│  - Attachment        │                             │  - attachments        │
│  - TicketActivity    │                             │  - notifications      │
└─────────────────────────────────────────────────────────────────────────────┘
```

## Design Patterns Used

### 1. Repository Pattern
Abstracts data access logic from business logic.

```php
// TicketRepository.php
class TicketRepository extends BaseRepository
{
    public function getFiltered(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['creator', 'assignee', 'category']);
        // Apply filters...
        return $query->paginate($perPage);
    }
    
    public function getStatistics(?int $userId = null): array
    {
        return Cache::remember($cacheKey, 300, function () {
            // Calculate statistics...
        });
    }
}
```

### 2. Service Layer Pattern
Contains business logic and orchestrates operations.

```php
// TicketService.php
class TicketService
{
    public function create(array $data, ?array $files): Ticket
    {
        return DB::transaction(function () use ($data, $files) {
            $ticket = $this->ticketRepository->create($data);
            
            if ($data['assigned_to']) {
                $this->assignTicket($ticket, $data['assigned_to']);
            }
            
            if ($files) {
                $this->handleFileUploads($ticket, $files);
            }
            
            event(new TicketCreated($ticket));
            
            return $ticket;
        });
    }
}
```

### 3. Observer Pattern (Events)
Used for real-time notifications via Pusher.

```php
// TicketAssigned event
class TicketAssigned implements ShouldBroadcast
{
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->assignee->id),
            new Channel('tickets'),
        ];
    }
}
```

### 4. Strategy Pattern (Enums)
Encapsulates status/priority behavior.

```php
// TicketStatus.php
enum TicketStatus: string
{
    case ASSIGNED = 'assigned';
    case IN_PROCESS = 'in_process';
    case COMPLETED = 'completed';
    
    public function canTransitionTo(TicketStatus $newStatus): bool
    {
        return match($this) {
            self::ASSIGNED => in_array($newStatus, [self::IN_PROCESS, self::CANCELLED]),
            self::IN_PROCESS => in_array($newStatus, [self::COMPLETED, self::ON_HOLD]),
            // ...
        };
    }
}
```

## Role-Based Access Control

### Roles & Permissions Matrix

| Permission | Admin | Manager | Developer |
|-----------|-------|---------|-----------|
| view_tickets | ✅ | ✅ | ✅ (own) |
| create_tickets | ✅ | ✅ | ❌ |
| edit_tickets | ✅ | ✅ | ❌ |
| delete_tickets | ✅ | ❌ | ❌ |
| assign_tickets | ✅ | ✅ | ❌ |
| update_ticket_status | ✅ | ✅ | ✅ (own) |
| view_all_tickets | ✅ | ✅ | ❌ |
| manage_categories | ✅ | ❌ | ❌ |
| manage_users | ✅ | ❌ | ❌ |

### Implementation

```php
// User model with Spatie HasRoles trait
class User extends Authenticatable
{
    use HasRoles;
    
    public function canManageTickets(): bool
    {
        return $this->hasAnyRole(['admin', 'manager']);
    }
}

// TicketPolicy.php
public function updateStatus(User $user, Ticket $ticket): bool
{
    if ($user->canManageTickets()) return true;
    return $ticket->assigned_to === $user->id;
}
```

## Real-Time Notification Flow

```
1. Manager assigns ticket
           │
           ▼
2. TicketService::assignTicket()
           │
           ▼
3. event(new TicketAssigned($ticket, $assignee))
           │
           ▼
4. Pusher broadcasts to private-user.{id} channel
           │
           ▼
5. JavaScript Pusher client receives event
           │
           ▼
6. Toast notification displayed + badge updated
           │
           ▼
7. SendTicketAssignedEmail job queued
           │
           ▼
8. Queue worker sends email
```

## Database Schema

### Core Tables

```sql
-- users (with Spatie roles)
users: id, name, email, password, phone, avatar, department, bio, is_active, 
       last_login_at, timestamps, soft_deletes

-- tickets (main entity)
tickets: id, ticket_number, title, description, status, priority,
         category_id, created_by, assigned_to, assigned_by,
         assigned_at, started_at, completed_at, due_date,
         estimated_hours, actual_hours, resolution_notes,
         timestamps, soft_deletes

-- Indexes: status, priority, created_at, (status, assigned_to), 
--          (status, priority), (assigned_to, status)
```

### Relationships

```
User ──1:N──> Ticket (created_by)
User ──1:N──> Ticket (assigned_to)
Category ──1:N──> Ticket
Ticket ──1:N──> Comment
Ticket ──1:N──> Attachment (morphMany)
Ticket ──1:N──> TicketActivity
User <──N:M──> Role <──N:M──> Permission
```

## Performance Optimizations

### 1. Database Caching
```php
// Cached for 5 minutes
public function getStatistics(): array
{
    return Cache::remember('ticket_stats', 300, function () {
        // Heavy queries...
    });
}
```

### 2. Eager Loading
```php
// Prevent N+1 queries
$tickets = Ticket::with(['creator', 'assignee', 'category'])->get();
```

### 3. Database Indexes
```php
// Composite indexes for common queries
$table->index(['status', 'assigned_to']);
$table->index(['status', 'priority']);
```

### 4. Query Scopes
```php
// Reusable query builders
public function scopeByStatus($query, $status) { ... }
public function scopeHighPriority($query) { ... }
```

## Security Measures

1. **CSRF Protection**: All forms include `@csrf`
2. **Rate Limiting**: Login (5/min), Register (3/min), Status updates (30/min)
3. **Authorization Policies**: TicketPolicy checks permissions
4. **Form Request Validation**: Type-safe input validation
5. **Role Middleware**: Route-level access control
6. **Soft Deletes**: Data preservation

## Queue System

### Jobs
- `SendTicketAssignedEmail` - Notifies developer of new assignment
- `SendTicketStatusEmail` - Notifies stakeholders of status changes

### Configuration
```env
QUEUE_CONNECTION=database  # or redis for production
```

### Commands
```bash
php artisan queue:work              # Start worker
php artisan queue:work --tries=3    # With retry
```

## Deployment Checklist

1. Set `APP_ENV=production` and `APP_DEBUG=false`
2. Run `php artisan config:cache`
3. Run `php artisan route:cache`
4. Run `php artisan view:cache`
5. Configure Pusher credentials
6. Configure SMTP for emails
7. Set up queue worker (Supervisor recommended)
8. Configure proper file permissions
9. Set up SSL certificate
10. Configure backup strategy

## Future Enhancements

1. **Two-Factor Authentication**
2. **Advanced Reporting & Analytics**
3. **RESTful API for Mobile Apps**
4. **Webhook Integrations (Slack, Teams)**
5. **SLA Management**
6. **Knowledge Base Integration**
7. **Customer Portal**
8. **Time Tracking**

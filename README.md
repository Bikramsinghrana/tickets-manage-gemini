# TicketPro - Professional IT Ticket Management System

A production-quality Laravel 10.10 application for IT ticket management with role-based access control using Spatie Laravel Permission, real-time notifications via Pusher, and a professional UI built with Bootstrap 5 and Tailwind CSS.

## Features

### Role-Based Access Control
- **Admin**: Full system access, user management, category management
- **Manager**: Create tickets, assign to developers, view reports
- **Developer**: View assigned tickets, update status via AJAX

### Ticket Management
- Complete CRUD operations with soft deletes
- Status workflow: Assigned → In Process → Completed (with On Hold, Cancelled)
- Priority levels: Low, Medium, High, Urgent
- File attachments (images, documents)
- Comments system

### Real-Time Notifications
- Pusher WebSocket integration
- Instant notifications when tickets are assigned
- Notification bell with unread count
- Mark as read functionality

### Performance Optimizations
- Repository pattern for data access
- Database caching for lists
- Eager loading to prevent N+1 queries
- Database indexes on frequently filtered columns
- Database transactions with commit/rollback

### Security
- CSRF protection
- Rate limiting on forms
- Role-based middleware
- Form request validation
- Soft deletes for data preservation

## System Requirements

- **PHP**: ^8.1
- **Laravel**: ^10.10
- **MySQL/PostgreSQL/SQLite**
- **Node.js**: For frontend build tools
- **Composer**: For PHP dependencies

## Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd ticket_manage
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Frontend Dependencies
```bash
npm install
```

### 4. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configure Database
Edit `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ticket_manage
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 6. Configure Pusher (Real-Time Notifications)
Create an account at https://pusher.com and add credentials to `.env`:
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster
```

### 7. Configure Email (Queued Jobs)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@ticketpro.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 8. Run Migrations & Seeders
```bash
php artisan migrate:fresh --seed
```

### 9. Create Storage Link
```bash
php artisan storage:link
```

### 10. Build Frontend Assets
```bash
npm run build
# OR for development with hot reload
npm run dev
```

### 11. Start the Application
```bash
php artisan serve
```

### 12. Start Queue Worker (for email notifications)
```bash
php artisan queue:work
```

Visit: http://localhost:8000

## Test Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@gmailcom | password |
| Manager | manager@gmail.com | password |
| Manager | manager1@youpmail.com | password |
| Developer | ranabikram8757@gmail.com | password |
| Developer | user2@yopmail.com | password |
| Developer | david.kumar@ticketmanager.com | password |
| Developer | lisa.thompson@ticketmanager.com | password |
| Developer | alex.rodriguez@ticketmanager.com | password |

## Workflow

### Admin/Manager Workflow
1. Login to dashboard
2. Create new ticket (standard POST form)
3. Assign ticket to developer from dropdown
4. Set priority and upload files
5. Track ticket progress on dashboard

### Developer Workflow
1. Receive real-time notification when assigned
2. View assigned tickets on dashboard
3. Update ticket status via AJAX (In Process → Completed)
4. Add comments and attachments

## Form Handling

| Action | Method | Used By |
|--------|--------|---------|
| Create Ticket | POST (Standard Form) | Admin, Manager |
| Update Ticket | POST (Standard Form) | Admin, Manager |
| Assign Ticket | POST (Standard Form) | Admin, Manager |
| Update Status | AJAX (PATCH) | Developer |
| Add Comment | AJAX (POST) | All Users |
| Delete Comment | AJAX (DELETE) | Comment Author, Admin |

## API Endpoints

### Tickets
- `GET /tickets` - List tickets
- `GET /tickets/create` - Create form
- `POST /tickets` - Store ticket
- `GET /tickets/{id}` - View ticket
- `GET /tickets/{id}/edit` - Edit form
- `PUT /tickets/{id}` - Update ticket
- `DELETE /tickets/{id}` - Delete ticket
- `PATCH /tickets/{id}/status` - Update status (AJAX)

### Notifications
- `GET /notifications` - All notifications
- `GET /notifications/recent` - Recent (AJAX)
- `GET /notifications/unread-count` - Count (AJAX)
- `POST /notifications/{id}/read` - Mark read (AJAX)
- `POST /notifications/mark-all-read` - Mark all read (AJAX)

### Comments
- `POST /comments` - Add comment (AJAX)
- `DELETE /comments/{id}` - Delete comment (AJAX)

## Project Structure

```
app/
├── Enums/                  # Status, Priority, Role, Permission enums
├── Events/                 # Broadcasting events (TicketCreated, TicketAssigned, etc.)
├── Http/
│   ├── Controllers/        # Request handlers
│   ├── Middleware/         # RoleMiddleware, CheckPermission
│   └── Requests/           # Form validation
├── Jobs/                   # Queued email jobs
├── Mail/                   # Mailable classes
├── Models/                 # Eloquent models with Spatie traits
├── Notifications/          # Database & broadcast notifications
├── Policies/               # Authorization policies
├── Repositories/           # Data access layer (Repository pattern)
└── Services/               # Business logic layer

database/
├── migrations/             # Table definitions with indexes
└── seeders/                # Roles, Users, Categories, Tickets

resources/views/
├── layouts/                # Main layout, sidebar, navbar
├── auth/                   # Login, Register
├── dashboard/              # Admin and Developer dashboards
├── tickets/                # CRUD views
├── categories/             # Category management
├── notifications/          # Notification list
└── emails/                 # Email templates
```

## License

MIT

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Category;
use App\Enums\TicketStatus;
use App\Enums\TicketPriority;
use App\Enums\Role;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::role(Role::ADMIN->value)->first();
        $managers = User::role(Role::MANAGER->value)->get();
        $developers = User::role(Role::DEVELOPER->value)->get();
        $categories = Category::all();

        if (!$admin || $managers->isEmpty() || $developers->isEmpty() || $categories->isEmpty()) {
            $this->command->warn('Please run RolePermissionSeeder, UserSeeder, and CategorySeeder first.');
            return;
        }

        $tickets = [
            [
                'title' => 'Login page not loading on mobile devices',
                'description' => 'Users are reporting that the login page fails to load properly on iOS Safari and Android Chrome browsers. The page appears blank with only the header visible. This is affecting approximately 30% of our mobile users.',
                'status' => TicketStatus::ASSIGNED,
                'priority' => TicketPriority::URGENT,
                'category' => 'bug-fix',
                'assigned_to' => $developers[0]->id,
                'due_date' => now()->addDays(2),
            ],
            [
                'title' => 'Add dark mode support to dashboard',
                'description' => 'Implement a dark mode toggle for the entire dashboard. This should include all pages, modals, and components. User preference should be saved and persisted across sessions.',
                'status' => TicketStatus::IN_PROCESS,
                'priority' => TicketPriority::MEDIUM,
                'category' => 'feature-request',
                'assigned_to' => $developers[1]->id,
                'due_date' => now()->addDays(7),
                'started_at' => now()->subDays(2),
            ],
            [
                'title' => 'Optimize database queries on reports page',
                'description' => 'The reports page is taking over 10 seconds to load for large datasets. Need to analyze and optimize the SQL queries, possibly add indexes or implement caching.',
                'status' => TicketStatus::IN_PROCESS,
                'priority' => TicketPriority::HIGH,
                'category' => 'database',
                'assigned_to' => $developers[2]->id,
                'due_date' => now()->addDays(5),
                'started_at' => now()->subDays(1),
                'estimated_hours' => 16,
            ],
            [
                'title' => 'SSL certificate renewal',
                'description' => 'The SSL certificate for the production server expires in 10 days. Need to renew and deploy the new certificate with zero downtime.',
                'status' => TicketStatus::ASSIGNED,
                'priority' => TicketPriority::HIGH,
                'category' => 'infrastructure',
                'assigned_to' => $developers[3]->id,
                'due_date' => now()->addDays(7),
                'estimated_hours' => 4,
            ],
            [
                'title' => 'Implement two-factor authentication',
                'description' => 'Add optional two-factor authentication (2FA) for all user accounts. Support both TOTP (Google Authenticator) and SMS verification methods.',
                'status' => TicketStatus::ASSIGNED,
                'priority' => TicketPriority::HIGH,
                'category' => 'security',
                'assigned_to' => $developers[0]->id,
                'due_date' => now()->addDays(14),
                'estimated_hours' => 40,
            ],
            [
                'title' => 'Update API documentation for v2 endpoints',
                'description' => 'Complete documentation for all new v2 API endpoints including request/response examples, authentication requirements, and rate limits.',
                'status' => TicketStatus::COMPLETED,
                'priority' => TicketPriority::LOW,
                'category' => 'documentation',
                'assigned_to' => $developers[1]->id,
                'completed_at' => now()->subDays(1),
                'actual_hours' => 8,
            ],
            [
                'title' => 'Fix broken email notifications',
                'description' => 'Email notifications for new ticket assignments are not being sent. The queue seems to be processing but emails are not delivered. Need to investigate SMTP configuration and queue worker.',
                'status' => TicketStatus::ASSIGNED,
                'priority' => TicketPriority::URGENT,
                'category' => 'bug-fix',
                'assigned_to' => $developers[2]->id,
                'due_date' => now()->addDays(1),
            ],
            [
                'title' => 'Improve search functionality with filters',
                'description' => 'Enhance the global search to support filtering by date range, category, status, and assigned user. Also implement search suggestions based on previous searches.',
                'status' => TicketStatus::ON_HOLD,
                'priority' => TicketPriority::MEDIUM,
                'category' => 'enhancement',
                'assigned_to' => $developers[4]->id,
                'due_date' => now()->addDays(21),
                'estimated_hours' => 24,
            ],
            [
                'title' => 'Memory leak in notification system',
                'description' => 'The WebSocket connection for real-time notifications appears to have a memory leak. Browser memory usage increases over time until the page becomes unresponsive.',
                'status' => TicketStatus::IN_PROCESS,
                'priority' => TicketPriority::HIGH,
                'category' => 'bug-fix',
                'assigned_to' => $developers[3]->id,
                'due_date' => now()->addDays(3),
                'started_at' => now()->subHours(6),
                'estimated_hours' => 12,
            ],
            [
                'title' => 'Add export to Excel feature for tickets',
                'description' => 'Allow managers and admins to export ticket data to Excel format. Should include all ticket fields with proper formatting and support for filtered exports.',
                'status' => TicketStatus::ASSIGNED,
                'priority' => TicketPriority::LOW,
                'category' => 'feature-request',
                'assigned_to' => $developers[4]->id,
                'due_date' => now()->addDays(10),
                'estimated_hours' => 8,
            ],
            [
                'title' => 'Setup staging environment',
                'description' => 'Create a staging environment that mirrors production for testing new features before deployment. Include automated deployment pipeline from develop branch.',
                'status' => TicketStatus::COMPLETED,
                'priority' => TicketPriority::HIGH,
                'category' => 'infrastructure',
                'assigned_to' => $developers[3]->id,
                'completed_at' => now()->subDays(3),
                'actual_hours' => 20,
            ],
            [
                'title' => 'Performance testing and optimization',
                'description' => 'Conduct comprehensive performance testing using load testing tools. Identify bottlenecks and implement optimizations to handle 10x current traffic.',
                'status' => TicketStatus::ASSIGNED,
                'priority' => TicketPriority::MEDIUM,
                'category' => 'enhancement',
                'assigned_to' => $developers[2]->id,
                'due_date' => now()->addDays(30),
                'estimated_hours' => 60,
            ],
        ];

        $managerIndex = 0;
        foreach ($tickets as $ticketData) {
            $category = $categories->where('slug', $ticketData['category'])->first();
            $manager = $managers[$managerIndex % $managers->count()];
            
            Ticket::create([
                'title' => $ticketData['title'],
                'description' => $ticketData['description'],
                'status' => $ticketData['status'],
                'priority' => $ticketData['priority'],
                'category_id' => $category?->id,
                'created_by' => $manager->id,
                'assigned_to' => $ticketData['assigned_to'],
                'assigned_by' => $manager->id,
                'assigned_at' => now()->subDays(rand(1, 7)),
                'due_date' => $ticketData['due_date'] ?? null,
                'started_at' => $ticketData['started_at'] ?? null,
                'completed_at' => $ticketData['completed_at'] ?? null,
                'estimated_hours' => $ticketData['estimated_hours'] ?? null,
                'actual_hours' => $ticketData['actual_hours'] ?? null,
            ]);

            $managerIndex++;
        }

        $this->command->info('Tickets seeded successfully! Created ' . count($tickets) . ' tickets.');
    }
}

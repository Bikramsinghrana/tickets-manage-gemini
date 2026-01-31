<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'phone' => '+91-875550100',
                'department' => 'IT Administration',
                'bio' => 'System administrator with full access to all features.',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole(Role::ADMIN->value);

        // Manager Users
        $managers = [
            [
                'name' => 'John Manager',
                'email' => 'manager@gmail.com',
                'phone' => '+91-85550201',
                'department' => 'Project Management',
                'bio' => 'Project manager overseeing development tickets and team assignments.',
            ],
            [
                'name' => 'Sarah Wilson',
                'email' => 'manager1@youpmail.com',
                'phone' => '+1-555-0202',
                'department' => 'IT Support',
                'bio' => 'IT Support manager handling support ticket assignments.',
            ],
        ];

        foreach ($managers as $managerData) {
            $manager = User::firstOrCreate(
                ['email' => $managerData['email']],
                array_merge($managerData, [
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ])
            );
            $manager->assignRole(Role::MANAGER->value);
        }

        // Developer Users
        $developers = [
            [
                'name' => 'Rana',
                'email' => 'ranabikram8757@gmail.com',
                'phone' => '+19-8765550301',
                'department' => 'Backend Development',
                'bio' => 'Senior backend developer specializing in PHP and Laravel.',
            ],
            [
                'name' => 'Bikram Singh',
                'email' => 'user2@yopmail.com',
                'phone' => '+1-555-0302',
                'department' => 'Frontend Development',
                'bio' => 'Frontend developer with expertise in React and Vue.js.',
            ],
            [
                'name' => 'David Kumar',
                'email' => 'david.kumar@ticketmanager.com',
                'phone' => '+1-555-0303',
                'department' => 'Full Stack Development',
                'bio' => 'Full stack developer handling both frontend and backend tasks.',
            ],
            [
                'name' => 'Lisa Thompson',
                'email' => 'lisa.thompson@ticketmanager.com',
                'phone' => '+1-555-0304',
                'department' => 'DevOps',
                'bio' => 'DevOps engineer managing infrastructure and deployments.',
            ],
            [
                'name' => 'Alex Rodriguez',
                'email' => 'alex.rodriguez@ticketmanager.com',
                'phone' => '+1-555-0305',
                'department' => 'Mobile Development',
                'bio' => 'Mobile app developer working on iOS and Android applications.',
            ],
        ];

        foreach ($developers as $developerData) {
            $developer = User::firstOrCreate(
                ['email' => $developerData['email']],
                array_merge($developerData, [
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ])
            );
            $developer->assignRole(Role::DEVELOPER->value);
        }

        $this->command->info('Users seeded successfully!');
        $this->command->table(
            ['Role', 'Email', 'Name'],
            [
                ['Admin', 'admin@gmailcom', 'System Administrator'],
                ['Manager', 'manager@gmail.com', 'John Manager'],
                ['Manager', 'manager1@youpmail.com', 'Sarah Wilson'],
                ['Developer', 'ranabikram8757@gmail.com', 'Rana'],
                ['Developer', 'user2@yopmail.com', 'Bikram Singh'],
                ['Developer', 'david.kumar@ticketmanager.com', 'David Kumar'],
                ['Developer', 'lisa.thompson@ticketmanager.com', 'Lisa Thompson'],
                ['Developer', 'alex.rodriguez@ticketmanager.com', 'Alex Rodriguez'],
            ]
        );
    }
}

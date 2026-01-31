<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Bug Fix',
                'slug' => 'bug-fix',
                'description' => 'Software bugs and defects that need to be resolved',
                'color' => '#ef4444',
                'icon' => 'fa-bug',
                'sort_order' => 1,
            ],
            [
                'name' => 'Feature Request',
                'slug' => 'feature-request',
                'description' => 'New feature development requests',
                'color' => '#8b5cf6',
                'icon' => 'fa-lightbulb',
                'sort_order' => 2,
            ],
            [
                'name' => 'Enhancement',
                'slug' => 'enhancement',
                'description' => 'Improvements to existing features',
                'color' => '#06b6d4',
                'icon' => 'fa-arrow-up',
                'sort_order' => 3,
            ],
            [
                'name' => 'Technical Support',
                'slug' => 'technical-support',
                'description' => 'Technical assistance and troubleshooting',
                'color' => '#f59e0b',
                'icon' => 'fa-headset',
                'sort_order' => 4,
            ],
            [
                'name' => 'Infrastructure',
                'slug' => 'infrastructure',
                'description' => 'Server, deployment, and infrastructure related tasks',
                'color' => '#10b981',
                'icon' => 'fa-server',
                'sort_order' => 5,
            ],
            [
                'name' => 'Database',
                'slug' => 'database',
                'description' => 'Database related tasks and optimizations',
                'color' => '#3b82f6',
                'icon' => 'fa-database',
                'sort_order' => 6,
            ],
            [
                'name' => 'Security',
                'slug' => 'security',
                'description' => 'Security vulnerabilities and improvements',
                'color' => '#dc2626',
                'icon' => 'fa-shield-alt',
                'sort_order' => 7,
            ],
            [
                'name' => 'Documentation',
                'slug' => 'documentation',
                'description' => 'Documentation updates and improvements',
                'color' => '#6b7280',
                'icon' => 'fa-file-alt',
                'sort_order' => 8,
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['slug' => $categoryData['slug']],
                array_merge($categoryData, ['is_active' => true])
            );
        }

        $this->command->info('Categories seeded successfully!');
    }
}

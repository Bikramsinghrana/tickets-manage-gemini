<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Enums\Role as RoleEnum;
use App\Enums\Permission as PermissionEnum;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        foreach (PermissionEnum::cases() as $permission) {
            Permission::firstOrCreate([
                'name' => $permission->value,
                'guard_name' => 'web',
            ], [
                'description' => $permission->description(),
            ]);
        }

        // Create roles and assign permissions
        
        // Admin Role - All permissions
        $adminRole = Role::firstOrCreate([
            'name' => RoleEnum::ADMIN->value,
            'guard_name' => 'web',
        ], [
            'description' => RoleEnum::ADMIN->description(),
        ]);
        $adminRole->syncPermissions(PermissionEnum::adminPermissions());

        // Manager Role - Create, assign, view all tickets
        $managerRole = Role::firstOrCreate([
            'name' => RoleEnum::MANAGER->value,
            'guard_name' => 'web',
        ], [
            'description' => RoleEnum::MANAGER->description(),
        ]);
        $managerRole->syncPermissions(PermissionEnum::managerPermissions());

        // Developer Role - View and update assigned tickets
        $developerRole = Role::firstOrCreate([
            'name' => RoleEnum::DEVELOPER->value,
            'guard_name' => 'web',
        ], [
            'description' => RoleEnum::DEVELOPER->description(),
        ]);
        $developerRole->syncPermissions(PermissionEnum::developerPermissions());

        $this->command->info('Roles and Permissions seeded successfully!');
        $this->command->table(
            ['Role', 'Permissions Count'],
            [
                ['Admin', count(PermissionEnum::adminPermissions())],
                ['Manager', count(PermissionEnum::managerPermissions())],
                ['Developer', count(PermissionEnum::developerPermissions())],
            ]
        );
    }
}

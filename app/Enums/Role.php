<?php

namespace App\Enums;

/**
 * Application Roles
 * Used with Spatie Laravel Permission
 */
enum Role: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case DEVELOPER = 'developer';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::MANAGER => 'Manager',
            self::DEVELOPER => 'Developer',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match($this) {
            self::ADMIN => 'Full system access with all permissions',
            self::MANAGER => 'Can create and assign tickets, view reports',
            self::DEVELOPER => 'Can view and update status of assigned tickets',
        };
    }

    /**
     * Get badge color
     */
    public function color(): string
    {
        return match($this) {
            self::ADMIN => 'danger',
            self::MANAGER => 'primary',
            self::DEVELOPER => 'success',
        };
    }

    /**
     * Get Tailwind background class
     */
    public function tailwindBgClass(): string
    {
        return match($this) {
            self::ADMIN => 'bg-red-100 text-red-800',
            self::MANAGER => 'bg-blue-100 text-blue-800',
            self::DEVELOPER => 'bg-green-100 text-green-800',
        };
    }

    /**
     * Get Bootstrap badge class
     */
    public function badgeClass(): string
    {
        return 'badge bg-' . $this->color();
    }

    /**
     * Get all role values
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get options for select dropdown
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn($role) => [
            $role->value => $role->label()
        ])->toArray();
    }

    /**
     * Get roles that can be assigned tickets
     */
    public static function assignableRoles(): array
    {
        return [self::DEVELOPER->value];
    }
}

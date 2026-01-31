<?php

namespace App\Enums;

/**
 * Application Permissions
 * Used with Spatie Laravel Permission
 */
enum Permission: string
{
    // Ticket Permissions
    case VIEW_TICKETS = 'view_tickets';
    case CREATE_TICKETS = 'create_tickets';
    case EDIT_TICKETS = 'edit_tickets';
    case DELETE_TICKETS = 'delete_tickets';
    case ASSIGN_TICKETS = 'assign_tickets';
    case UPDATE_TICKET_STATUS = 'update_ticket_status';
    case VIEW_ALL_TICKETS = 'view_all_tickets';
    
    // Category Permissions
    case VIEW_CATEGORIES = 'view_categories';
    case CREATE_CATEGORIES = 'create_categories';
    case EDIT_CATEGORIES = 'edit_categories';
    case DELETE_CATEGORIES = 'delete_categories';
    
    // User Management
    case VIEW_USERS = 'view_users';
    case CREATE_USERS = 'create_users';
    case EDIT_USERS = 'edit_users';
    case DELETE_USERS = 'delete_users';
    case ASSIGN_ROLES = 'assign_roles';
    
    // Reports
    case VIEW_REPORTS = 'view_reports';
    case EXPORT_REPORTS = 'export_reports';
    
    // Settings
    case MANAGE_SETTINGS = 'manage_settings';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match($this) {
            self::VIEW_TICKETS => 'Can view assigned tickets',
            self::CREATE_TICKETS => 'Can create new tickets',
            self::EDIT_TICKETS => 'Can edit ticket details',
            self::DELETE_TICKETS => 'Can delete tickets',
            self::ASSIGN_TICKETS => 'Can assign tickets to users',
            self::UPDATE_TICKET_STATUS => 'Can update ticket status',
            self::VIEW_ALL_TICKETS => 'Can view all tickets in the system',
            self::VIEW_CATEGORIES => 'Can view categories',
            self::CREATE_CATEGORIES => 'Can create categories',
            self::EDIT_CATEGORIES => 'Can edit categories',
            self::DELETE_CATEGORIES => 'Can delete categories',
            self::VIEW_USERS => 'Can view user list',
            self::CREATE_USERS => 'Can create new users',
            self::EDIT_USERS => 'Can edit user details',
            self::DELETE_USERS => 'Can delete users',
            self::ASSIGN_ROLES => 'Can assign roles to users',
            self::VIEW_REPORTS => 'Can view reports and analytics',
            self::EXPORT_REPORTS => 'Can export reports',
            self::MANAGE_SETTINGS => 'Can manage system settings',
        };
    }

    /**
     * Get all permission values
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get permissions for Admin role
     */
    public static function adminPermissions(): array
    {
        return self::toArray(); // Admin has all permissions
    }

    /**
     * Get permissions for Manager role
     */
    public static function managerPermissions(): array
    {
        return [
            self::VIEW_TICKETS->value,
            self::CREATE_TICKETS->value,
            self::EDIT_TICKETS->value,
            self::ASSIGN_TICKETS->value,
            self::VIEW_ALL_TICKETS->value,
            self::VIEW_CATEGORIES->value,
            self::VIEW_USERS->value,
            self::VIEW_REPORTS->value,
        ];
    }

    /**
     * Get permissions for Developer role
     */
    public static function developerPermissions(): array
    {
        return [
            self::VIEW_TICKETS->value,
            self::UPDATE_TICKET_STATUS->value,
            self::VIEW_CATEGORIES->value,
        ];
    }
}

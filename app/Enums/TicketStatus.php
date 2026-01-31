<?php

namespace App\Enums;

enum TicketStatus: string
{
    case ASSIGNED = 'assigned';
    case IN_PROCESS = 'in_process';
    case COMPLETED = 'completed';
    case ON_HOLD = 'on_hold';
    case CANCELLED = 'cancelled';

    /**
     * Get the human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::ASSIGNED => 'Assigned',
            self::IN_PROCESS => 'In Process',
            self::COMPLETED => 'Completed',
            self::ON_HOLD => 'On Hold',
            self::CANCELLED => 'Cancelled',
        };
    }

    /**
     * Get the Bootstrap/Tailwind color class
     */
    public function color(): string
    {
        return match($this) {
            self::ASSIGNED => 'info',
            self::IN_PROCESS => 'warning',
            self::COMPLETED => 'success',
            self::ON_HOLD => 'secondary',
            self::CANCELLED => 'danger',
        };
    }

    /**
     * Get Tailwind background color class
     */
    public function tailwindBgClass(): string
    {
        return match($this) {
            self::ASSIGNED => 'bg-blue-100 text-blue-800',
            self::IN_PROCESS => 'bg-amber-100 text-amber-800',
            self::COMPLETED => 'bg-green-100 text-green-800',
            self::ON_HOLD => 'bg-gray-100 text-gray-800',
            self::CANCELLED => 'bg-red-100 text-red-800',
        };
    }

    /**
     * Get the icon class
     */
    public function icon(): string
    {
        return match($this) {
            self::ASSIGNED => 'fa-user-check',
            self::IN_PROCESS => 'fa-spinner',
            self::COMPLETED => 'fa-check-circle',
            self::ON_HOLD => 'fa-pause-circle',
            self::CANCELLED => 'fa-times-circle',
        };
    }

    /**
     * Get the Bootstrap badge class
     */
    public function badgeClass(): string
    {
        return 'badge bg-' . $this->color();
    }

    /**
     * Check if status can transition to another status
     */
    public function canTransitionTo(TicketStatus $newStatus): bool
    {
        return match($this) {
            self::ASSIGNED => in_array($newStatus, [self::IN_PROCESS, self::CANCELLED]),
            self::IN_PROCESS => in_array($newStatus, [self::COMPLETED, self::ON_HOLD, self::CANCELLED]),
            self::ON_HOLD => in_array($newStatus, [self::IN_PROCESS, self::CANCELLED]),
            self::COMPLETED => false, // Completed tickets cannot change status
            self::CANCELLED => false, // Cancelled tickets cannot change status
        };
    }

    /**
     * Get all statuses as array for dropdowns
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
        return collect(self::cases())->mapWithKeys(fn($status) => [
            $status->value => $status->label()
        ])->toArray();
    }

    /**
     * Get statuses a developer can change to (via AJAX)
     */
    public static function developerStatuses(): array
    {
        return [
            self::IN_PROCESS,
            self::COMPLETED,
            self::ON_HOLD,
        ];
    }
}

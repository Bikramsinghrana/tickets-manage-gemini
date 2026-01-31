<?php

namespace App\Enums;

enum TicketPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';

    /**
     * Get the human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High',
            self::URGENT => 'Urgent',
        };
    }

    /**
     * Get the Bootstrap/Tailwind color class
     */
    public function color(): string
    {
        return match($this) {
            self::LOW => 'success',
            self::MEDIUM => 'info',
            self::HIGH => 'warning',
            self::URGENT => 'danger',
        };
    }

    /**
     * Get Tailwind background color class
     */
    public function tailwindBgClass(): string
    {
        return match($this) {
            self::LOW => 'bg-green-100 text-green-800',
            self::MEDIUM => 'bg-blue-100 text-blue-800',
            self::HIGH => 'bg-amber-100 text-amber-800',
            self::URGENT => 'bg-red-100 text-red-800',
        };
    }

    /**
     * Get the icon class
     */
    public function icon(): string
    {
        return match($this) {
            self::LOW => 'fa-arrow-down',
            self::MEDIUM => 'fa-minus',
            self::HIGH => 'fa-arrow-up',
            self::URGENT => 'fa-exclamation-triangle',
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
     * Get numeric weight for sorting
     */
    public function weight(): int
    {
        return match($this) {
            self::LOW => 1,
            self::MEDIUM => 2,
            self::HIGH => 3,
            self::URGENT => 4,
        };
    }

    /**
     * Get all priorities as array
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
        return collect(self::cases())->mapWithKeys(fn($priority) => [
            $priority->value => $priority->label()
        ])->toArray();
    }
}

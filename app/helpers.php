<?php

use App\Enums\TicketStatus;
use App\Enums\TicketPriority;

if (!function_exists('perPage')) {
    /**
     * Get pagination limit from config or default
     */
    function perPage(?int $default = null): int
    {
        return (int) config('services.pagination.per_page', $default ?? 10);
    }
}

if (!function_exists('statusBadgeClass')) {
    /**
     * Get Bootstrap badge class for ticket status
     */
    function statusBadgeClass(string|TicketStatus $status): string
    {
        if (is_string($status)) {
            $status = TicketStatus::tryFrom($status);
        }
        
        return $status?->badgeClass() ?? 'badge bg-secondary';
    }
}

if (!function_exists('priorityBadgeClass')) {
    /**
     * Get Bootstrap badge class for ticket priority
     */
    function priorityBadgeClass(string|TicketPriority $priority): string
    {
        if (is_string($priority)) {
            $priority = TicketPriority::tryFrom($priority);
        }
        
        return $priority?->badgeClass() ?? 'badge bg-secondary';
    }
}

if (!function_exists('formatFileSize')) {
    /**
     * Format bytes to human readable size
     */
    function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

if (!function_exists('truncate')) {
    /**
     * Truncate string with ellipsis
     */
    function truncate(string $string, int $length = 100, string $suffix = '...'): string
    {
        if (strlen($string) <= $length) {
            return $string;
        }
        
        return substr($string, 0, $length) . $suffix;
    }
}

if (!function_exists('isActiveRoute')) {
    /**
     * Check if current route matches pattern
     */
    function isActiveRoute(string|array $patterns): bool
    {
        $patterns = is_array($patterns) ? $patterns : [$patterns];
        
        foreach ($patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }
        
        return false;
    }
}

if (!function_exists('activeClass')) {
    /**
     * Return active class if route matches
     */
    function activeClass(string|array $patterns, string $activeClass = 'active'): string
    {
        return isActiveRoute($patterns) ? $activeClass : '';
    }
}

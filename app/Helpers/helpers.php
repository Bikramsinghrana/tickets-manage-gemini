<?php

if (!function_exists('perPage')) {
    /**
     * Get the global pagination limit from config
     *
     * @return int
     */
    function perPage(): int
    {
        return (int) config('services.pagination.per_page', 10);
    }
}

if (!function_exists('statusBadge')) {
    /**
     * Generate HTML for status badge
     *
     * @param string $status
     * @return string
     */
    function statusBadge(string $status): string
    {
        $color = config('ticket.status_colors.' . $status, 'secondary');
        $label = ucfirst(str_replace('_', ' ', $status));
        return '<span class="badge bg-' . $color . '">' . $label . '</span>';
    }
}

if (!function_exists('priorityBadge')) {
    /**
     * Generate HTML for priority badge
     *
     * @param string $priority
     * @return string
     */
    function priorityBadge(string $priority): string
    {
        $color = config('ticket.priority_colors.' . $priority, 'secondary');
        $label = ucfirst($priority);
        $icon = '<i class="fas fa-flag me-1"></i>';
        return '<span class="badge bg-' . $color . '">' . $icon . $label . '</span>';
    }
}

if (!function_exists('formatFileSize')) {
    /**
     * Format file size in human-readable format
     *
     * @param int $bytes
     * @return string
     */
    function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < 3) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

if (!function_exists('activeCategories')) {
    /**
     * Get all active categories (cached)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function activeCategories()
    {
        return \Illuminate\Support\Facades\Cache::remember('active_categories', 3600, function () {
            return \App\Models\Category::where('is_active', true)->orderBy('name')->get();
        });
    }
}

<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CategoryRepository extends BaseRepository
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all active categories for dropdown (cached)
     */
    public function getForDropdown(): Collection
    {
        return Cache::remember('categories_dropdown', 3600, function () {
            return $this->model
                ->active()
                ->sorted()
                ->get(['id', 'name', 'color', 'icon']);
        });
    }

    /**
     * Get all categories with ticket counts
     */
    public function getAllWithCounts(): Collection
    {
        return $this->model
            ->withCount(['tickets', 'tickets as active_tickets_count' => function ($q) {
                $q->whereNotIn('status', ['completed', 'cancelled']);
            }])
            ->sorted()
            ->get();
    }

    /**
     * Clear category cache
     */
    public function clearCache(): void
    {
        Cache::forget('categories_dropdown');
    }

    /**
     * Find by slug
     */
    public function findBySlug(string $slug): ?Category
    {
        return $this->model->where('slug', $slug)->first();
    }

    /**
     * Update sort order for multiple categories
     */
    public function updateSortOrder(array $order): void
    {
        foreach ($order as $position => $categoryId) {
            $this->model->where('id', $categoryId)->update(['sort_order' => $position]);
        }
        $this->clearCache();
    }

    /**
     * Toggle active status
     */
    public function toggleActive(int $id): Category
    {
        $category = $this->findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);
        $this->clearCache();
        return $category->fresh();
    }
}

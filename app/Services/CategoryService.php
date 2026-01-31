<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryService
{
    /**
     * Create a new category
     */
    public function create(array $data): Category
    {
        $category = Category::create($data);
        $this->clearCache();
        return $category;
    }

    /**
     * Update an existing category
     */
    public function update(Category $category, array $data): Category
    {
        $category->update($data);
        $this->clearCache();
        return $category->fresh();
    }

    /**
     * Delete a category
     */
    public function delete(Category $category): bool
    {
        $result = $category->delete();
        $this->clearCache();
        return $result;
    }

    /**
     * Get all active categories
     */
    public function getActive()
    {
        return Cache::remember('active_categories', 3600, function () {
            return Category::active()->orderBy('name')->get();
        });
    }

    /**
     * Get all categories
     */
    public function getAll()
    {
        return Category::orderBy('name')->get();
    }

    /**
     * Get categories for dropdown
     */
    public function forDropdown()
    {
        return $this->getActive()
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Clear cache
     */
    private function clearCache(): void
    {
        Cache::forget('active_categories');
    }
}

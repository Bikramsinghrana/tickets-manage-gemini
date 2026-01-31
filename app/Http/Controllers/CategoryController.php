<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Http\Requests\CategoryRequest;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryRepository $categoryRepository
    ) {}

    /**
     * Display a listing of categories.
     */
    public function index()
    {
        $categories = $this->categoryRepository->getAllWithCounts();

        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created category.
     */
    public function store(CategoryRequest $request)
    {
        $this->categoryRepository->create($request->validated());
        $this->categoryRepository->clearCache();

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully!');
    }

    /**
     * Show the form for editing the category.
     */
    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified category.
     */
    public function update(CategoryRequest $request, Category $category)
    {
        $this->categoryRepository->update($category->id, $request->validated());
        $this->categoryRepository->clearCache();

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category)
    {
        // Check if category has tickets
        if ($category->tickets()->exists()) {
            return redirect()->route('categories.index')
                ->with('error', 'Cannot delete category with existing tickets.');
        }

        $this->categoryRepository->delete($category->id);
        $this->categoryRepository->clearCache();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully!');
    }

    /**
     * Toggle category active status
     */
    public function toggleActive(Category $category)
    {
        $this->categoryRepository->toggleActive($category->id);

        return response()->json([
            'success' => true,
            'is_active' => $category->fresh()->is_active,
        ]);
    }
}

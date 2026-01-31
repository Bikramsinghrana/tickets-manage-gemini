@extends('layouts.app')

@section('title', 'Categories')
@section('page-title', 'Category Management')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-folder me-2"></i>Categories</h6>
        <a href="{{ route('categories.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-2"></i>Add Category</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Color</th>
                        <th>Tickets</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td>
                            <i class="fas {{ $category->icon ?? 'fa-folder' }} me-2" style="color: {{ $category->color }}"></i>
                            <strong>{{ $category->name }}</strong>
                        </td>
                        <td class="text-muted">{{ $category->slug }}</td>
                        <td>
                            <span class="d-inline-block rounded" style="width: 20px; height: 20px; background: {{ $category->color }}"></span>
                            <code class="ms-2">{{ $category->color }}</code>
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $category->tickets_count }}</span>
                            <span class="badge bg-warning text-dark">{{ $category->active_tickets_count }} active</span>
                        </td>
                        <td>
                            @if($category->is_active)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete this category?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" {{ $category->tickets_count > 0 ? 'disabled' : '' }}><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No categories found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

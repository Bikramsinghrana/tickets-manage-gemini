@extends('layouts.app')

@section('title', 'Edit Ticket')
@section('page-title', 'Edit Ticket')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit {{ $ticket->ticket_number }}</h5></div>
            <div class="card-body">
                <form action="{{ route('tickets.update', $ticket) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $ticket->title) }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="5" required>{{ old('description', $ticket->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $ticket->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                @foreach($priorities as $value => $label)
                                <option value="{{ $value }}" {{ old('priority', $ticket->priority->value) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Assign To</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">Select Developer</option>
                                @foreach($developers as $developer)
                                <option value="{{ $developer->id }}" {{ old('assigned_to', $ticket->assigned_to) == $developer->id ? 'selected' : '' }}>{{ $developer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Due Date</label>
                            <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $ticket->due_date?->format('Y-m-d')) }}">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Estimated Hours</label>
                        <input type="number" name="estimated_hours" class="form-control" value="{{ old('estimated_hours', $ticket->estimated_hours) }}" step="0.5" min="0.5">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Add More Attachments</label>
                        <input type="file" name="attachments[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar">
                        <small class="text-muted">Max 5 files, 10MB each</small>
                    </div>
                    
                    @if($ticket->attachments->count())
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Current Attachments</label>
                        <div class="list-group">
                            @foreach($ticket->attachments as $attachment)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas {{ $attachment->icon }} me-2"></i>{{ $attachment->original_name }} ({{ $attachment->formatted_size }})</span>
                                <a href="{{ route('attachments.download', $attachment) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i></a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Ticket</button>
                        <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

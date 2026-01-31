@extends('layouts.app')

@section('title', $ticket->ticket_number)
@section('page-title', 'Ticket Details')

@section('content')
<div class="row">
    <div class="col-xl-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-primary me-2">{{ $ticket->ticket_number }}</span>
                    <span class="{{ $ticket->status->badgeClass() }}">{{ $ticket->status->label() }}</span>
                    <span class="{{ $ticket->priority->badgeClass() }} ms-1"><i class="fas {{ $ticket->priority->icon() }} me-1"></i>{{ $ticket->priority->label() }}</span>
                </div>
                <div class="d-flex gap-2">
                    @can('update', $ticket)
                    <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit me-1"></i>Edit</a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <h4 class="mb-3">{{ $ticket->title }}</h4>
                <div class="mb-4">{!! nl2br(e($ticket->description)) !!}</div>
                
                @if($ticket->attachments->count())
                <div class="mb-4">
                    <h6 class="fw-semibold mb-3"><i class="fas fa-paperclip me-2"></i>Attachments</h6>
                    <div class="row g-2">
                        @foreach($ticket->attachments as $attachment)
                        <div class="col-md-4">
                            <div class="border rounded p-2 d-flex align-items-center">
                                <i class="fas {{ $attachment->icon }} fa-2x text-muted me-3"></i>
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="mb-0 text-truncate small fw-semibold">{{ $attachment->original_name }}</p>
                                    <small class="text-muted">{{ $attachment->formatted_size }}</small>
                                </div>
                                <a href="{{ route('attachments.download', $attachment) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i></a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Developer Status Update (AJAX) -->
        @if(auth()->user()->isDeveloper() && $ticket->assigned_to === auth()->id() && count($availableStatuses) > 0)
        <div class="card mb-4">
            <div class="card-header bg-primary bg-opacity-10"><h6 class="mb-0 text-primary"><i class="fas fa-tasks me-2"></i>Update Status</h6></div>
            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap">
                    @foreach($availableStatuses as $status)
                    <button class="btn btn-outline-{{ $status->color() }} status-update-btn" data-status="{{ $status->value }}">
                        <i class="fas {{ $status->icon() }} me-1"></i>{{ $status->label() }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        
        <!-- Comments -->
        <div class="card" id="comments">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-comments me-2"></i>Comments ({{ $ticket->comments->count() }})</h6></div>
            <div class="card-body">
                <form id="commentForm" class="mb-4">
                    <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                    <textarea name="content" class="form-control mb-2" rows="3" placeholder="Write a comment..." required></textarea>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-paper-plane me-1"></i>Post Comment</button>
                </form>
                
                <div id="commentsList">
                    @forelse($ticket->comments as $comment)
                    <div class="d-flex mb-3 pb-3 border-bottom comment-item" data-id="{{ $comment->id }}">
                        <img src="{{ $comment->user->avatar_url }}" class="rounded-circle me-3" style="width: 40px; height: 40px;">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $comment->user->name }}</strong>
                                <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-0 mt-1">{{ $comment->content }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3" id="noComments">No comments yet</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Details</h6></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">Category</dt>
                    <dd class="col-7">{{ $ticket->category?->name ?? 'None' }}</dd>
                    
                    <dt class="col-5 text-muted">Created By</dt>
                    <dd class="col-7">{{ $ticket->creator->name }}</dd>
                    
                    <dt class="col-5 text-muted">Assigned To</dt>
                    <dd class="col-7">{{ $ticket->assignee?->name ?? 'Unassigned' }}</dd>
                    
                    <dt class="col-5 text-muted">Created</dt>
                    <dd class="col-7">{{ $ticket->created_at->format('M d, Y h:i A') }}</dd>
                    
                    @if($ticket->due_date)
                    <dt class="col-5 text-muted">Due Date</dt>
                    <dd class="col-7 {{ $ticket->is_overdue ? 'text-danger fw-bold' : '' }}">{{ $ticket->due_date->format('M d, Y') }}</dd>
                    @endif
                    
                    @if($ticket->estimated_hours)
                    <dt class="col-5 text-muted">Est. Hours</dt>
                    <dd class="col-7">{{ $ticket->estimated_hours }}h</dd>
                    @endif
                </dl>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Activity</h6></div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                @forelse($ticket->activities->take(10) as $activity)
                <div class="p-3 border-bottom">
                    <div class="d-flex">
                        <i class="fas {{ $activity->action_icon }} me-3 mt-1"></i>
                        <div>
                            <p class="mb-1"><strong>{{ $activity->user->name }}</strong> {{ $activity->action_label }}</p>
                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-3 text-center text-muted">No activity yet</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Status update (AJAX)
$('.status-update-btn').on('click', function() {
    const status = $(this).data('status');
    const $btn = $(this);
    
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    
    $.ajax({
        url: '{{ route('tickets.update-status', $ticket) }}',
        method: 'PATCH',
        data: { status: status },
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                setTimeout(() => location.reload(), 1000);
            }
        },
        error: function(xhr) {
            showToast(xhr.responseJSON?.message || 'Error', 'danger');
            $btn.prop('disabled', false);
        }
    });
});

// Comment submission (AJAX)
$('#commentForm').on('submit', function(e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $form.find('button[type="submit"]');
    
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    
    $.ajax({
        url: '{{ route('comments.store') }}',
        method: 'POST',
        data: $form.serialize(),
        success: function(response) {
            if (response.success) {
                $('#noComments').remove();
                const html = `
                    <div class="d-flex mb-3 pb-3 border-bottom comment-item" data-id="${response.comment.id}">
                        <img src="${response.comment.user.avatar}" class="rounded-circle me-3" style="width: 40px; height: 40px;">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <strong>${response.comment.user.name}</strong>
                                <small class="text-muted">${response.comment.created_at_human}</small>
                            </div>
                            <p class="mb-0 mt-1">${response.comment.content}</p>
                        </div>
                    </div>
                `;
                $('#commentsList').prepend(html);
                $form[0].reset();
                showToast('Comment added', 'success');
            }
        },
        error: function(xhr) {
            showToast(xhr.responseJSON?.message || 'Error', 'danger');
        },
        complete: function() {
            $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i>Post Comment');
        }
    });
});
</script>
@endpush

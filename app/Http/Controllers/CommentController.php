<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Ticket;
use App\Http\Requests\CommentRequest;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Store a newly created comment.
     */
    public function store(CommentRequest $request): JsonResponse
    {
        $ticket = Ticket::findOrFail($request->ticket_id);
        
        // Authorization check
        $this->authorize('view', $ticket);

        $comment = DB::transaction(function () use ($request, $ticket) {
            $comment = Comment::create([
                'content' => $request->content,
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'is_internal' => $request->boolean('is_internal'),
                'parent_id' => $request->parent_id,
            ]);

            // Send notification
            $this->notificationService->sendNewComment($ticket, $request->content);

            return $comment;
        });

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully.',
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'is_internal' => $comment->is_internal,
                'user' => [
                    'id' => auth()->id(),
                    'name' => auth()->user()->name,
                    'avatar' => auth()->user()->avatar_url,
                ],
                'created_at' => $comment->created_at->format('M d, Y h:i A'),
                'created_at_human' => $comment->created_at->diffForHumans(),
            ],
        ]);
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(Comment $comment): JsonResponse
    {
        // Only comment author or admin can delete
        if ($comment->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this comment.',
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully.',
        ]);
    }
}

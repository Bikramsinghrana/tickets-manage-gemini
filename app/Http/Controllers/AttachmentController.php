<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Services\FileUploadService;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class AttachmentController extends Controller
{
    public function __construct(
        protected FileUploadService $fileUploadService
    ) {}

    /**
     * Download an attachment
     */
    public function download(Attachment $attachment): Response
    {
        // Check access - user must have access to the parent ticket
        if ($attachment->attachable_type === 'App\Models\Ticket') {
            $this->authorize('view', $attachment->attachable);
        }

        try {
            $file = $this->fileUploadService->download($attachment);
            
            return response($file['content'])
                ->header('Content-Type', $file['mime_type'])
                ->header('Content-Disposition', 'attachment; filename="' . $file['filename'] . '"');
        } catch (\Exception $e) {
            abort(404, 'File not found');
        }
    }

    /**
     * Delete an attachment
     */
    public function destroy(Attachment $attachment): JsonResponse
    {
        // Only uploader or admin can delete
        if ($attachment->uploaded_by !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this attachment.',
            ], 403);
        }

        $this->fileUploadService->delete($attachment);

        return response()->json([
            'success' => true,
            'message' => 'Attachment deleted successfully.',
        ]);
    }
}

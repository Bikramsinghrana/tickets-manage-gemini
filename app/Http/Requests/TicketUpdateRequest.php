<?php

namespace App\Http\Requests;

use App\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TicketUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->canManageTickets();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string', 'max:10000'],
            'priority' => ['sometimes', 'required', 'string', Rule::in(TicketPriority::toArray())],
            'category_id' => ['nullable', 'exists:categories,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0.5', 'max:999'],
            'resolution_notes' => ['nullable', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => [
                'file',
                'max:10240', // 10MB
                'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt,zip,rar',
            ],
        ];
    }

    /**
     * Get custom error messages
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Please enter a ticket title.',
            'title.max' => 'Title cannot exceed 255 characters.',
            'description.required' => 'Please provide a description.',
            'priority.in' => 'Invalid priority selected.',
            'category_id.exists' => 'Selected category does not exist.',
            'assigned_to.exists' => 'Selected assignee does not exist.',
            'estimated_hours.min' => 'Estimated hours must be at least 0.5.',
            'attachments.max' => 'Maximum 5 attachments allowed.',
            'attachments.*.max' => 'Each file must be under 10MB.',
            'attachments.*.mimes' => 'Invalid file type.',
        ];
    }
}

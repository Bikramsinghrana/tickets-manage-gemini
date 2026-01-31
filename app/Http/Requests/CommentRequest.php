<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'ticket_id' => ['required', 'exists:tickets,id'],
            'content' => ['required', 'string', 'max:5000'],
            'is_internal' => ['nullable', 'boolean'],
            'parent_id' => ['nullable', 'exists:comments,id'],
        ];
    }

    /**
     * Get custom error messages
     */
    public function messages(): array
    {
        return [
            'ticket_id.required' => 'Ticket ID is required.',
            'ticket_id.exists' => 'Ticket not found.',
            'content.required' => 'Please enter your comment.',
            'content.max' => 'Comment cannot exceed 5000 characters.',
            'parent_id.exists' => 'Parent comment not found.',
        ];
    }
}

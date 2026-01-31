<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketRequest extends FormRequest
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
            'title'        => 'required|string|min:3|max:255',
            'description'  => 'required|string',
            'assigned_to'  => 'required|exists:users,id',
            'status'       => 'required|in:assign,process,complete',
        ];
    }


    public function messages(): array
    {
        return [
            'title.required' => 'Ticket title is required',
            'assigned_to.exists' => 'Assigned user does not exist',
            'status.in' => 'Invalid ticket status',
        ];
    }
}

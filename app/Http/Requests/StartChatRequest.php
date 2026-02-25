<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_token' => 'required|string|max:64',
            'visitor_name'  => 'nullable|string|max:255',
            'visitor_email' => 'nullable|email|max:255',
            'subject'       => 'nullable|string|max:500',
            'metadata'      => 'nullable|array',
        ];
    }
}

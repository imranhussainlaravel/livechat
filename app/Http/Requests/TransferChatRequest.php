<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'to_agent_id' => 'required|exists:users,id',
            'reason'      => 'nullable|string|max:500',
        ];
    }
}

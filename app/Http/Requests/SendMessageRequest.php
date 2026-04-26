<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'chat_id'  => 'required|integer|exists:chats,id',
            'message'  => 'required|string|max:5000',
            'metadata' => 'nullable|array',
        ];
    }

    /**
     * Merge route parameter 'id' into 'chat_id' if missing.
     */
    protected function prepareForValidation()
    {
        if ($this->route('id') && !$this->has('chat_id')) {
            $this->merge([
                'chat_id' => $this->route('id'),
            ]);
        }
    }
}

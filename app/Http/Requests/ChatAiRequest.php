<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatAiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['prompt' => ['required', 'string', 'max:1000']];
    }
}

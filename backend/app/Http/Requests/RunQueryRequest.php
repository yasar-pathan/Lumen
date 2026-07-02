<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RunQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prompt_version_id' => ['required', 'exists:prompt_versions,id'],
            'query' => ['required', 'string'],
            'provider' => ['nullable', 'string', 'in:mock,openrouter'],
        ];
    }
}

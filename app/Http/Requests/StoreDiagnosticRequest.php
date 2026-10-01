<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDiagnosticRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'message' => ['nullable', 'string', 'max:3000'],
            'services' => ['required', 'array', 'min:1'],
            'services.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('services', 'id')->where('is_active', true),
            ],
            'privacy' => ['accepted'],
        ];
    }
}
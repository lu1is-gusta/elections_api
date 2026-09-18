<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CandidacyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'election_id' => ['required', 'integer', 'exists:elections,id'],
            'uf' => ['nullable', 'string', 'size:2'],
            'office_id' => ['nullable', 'integer', 'exists:offices,id'],
            'party_id' => ['nullable', 'integer', 'exists:parties,id'],
            'electoral_unit_id' => ['nullable', 'integer', 'exists:electoral_units,id'],
            'q' => ['nullable', 'string', 'min:3'],
            'elected' => ['nullable', 'boolean'],
            'cursor' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('uf') && is_string($this->input('uf'))) {
            $this->merge([
                'uf' => strtoupper($this->input('uf')),
            ]);
        }
    }
}

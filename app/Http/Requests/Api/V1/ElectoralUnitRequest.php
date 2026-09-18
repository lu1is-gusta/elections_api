<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ElectoralUnitRequest extends FormRequest
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
            'kind' => ['required', 'in:pais,uf,municipio'],
            'uf' => ['nullable', 'string', 'size:2'],
            'parent_id' => ['nullable', 'integer', 'exists:electoral_units,id'],
            'q' => ['nullable', 'string'],
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('kind') === 'municipio' && ! $this->filled('uf') && ! $this->filled('parent_id')) {
                $validator->errors()->add('uf', 'The uf field is required when kind is municipio and parent_id is missing.');
            }
        });
    }
}

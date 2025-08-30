<?php

namespace App\Http\Requests;
use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;

class IndexHolidayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('Konfiguracja dni wolnych');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sort' => ['nullable', 'string', Rule::in(['name'])],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'filter' => ['nullable', 'string', 'max:100'],
            'show_archived' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'show_archived' => $this->boolean('show_archived'),
        ]);
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVatRateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('Edycja Produktów');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $vatRateId = $this->route('vat_rate')->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('vat_rates', 'name')->ignore($vatRateId)],
            'rate' => ['required', 'numeric', 'min:0', 'max:99.99'],
        ];
    }
}

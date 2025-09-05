<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitOfMeasureRequest extends FormRequest
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
        $unitOfMeasureId = $this->route('unit_of_measure')->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('unit_of_measures', 'name')->ignore($unitOfMeasureId)],
            'symbol' => ['required', 'string', 'max:10', Rule::unique('unit_of_measures', 'symbol')->ignore($unitOfMeasureId)],
        ];
    }
}

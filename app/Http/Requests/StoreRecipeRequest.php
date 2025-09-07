<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecipeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('Edycja Receptur');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'yield_quantity' => ['required', 'numeric', 'gt:0'],
            'yield_unit_of_measure_id' => ['required', 'integer', Rule::exists('unit_of_measures', 'id')],
            'is_active' => ['sometimes', 'boolean'],

            'ingredients' => ['required', 'array', 'min:1'],
            'ingredients.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'ingredients.*.quantity' => ['required', 'numeric', 'gt:0'],
            'ingredients.*.unit_of_measure_id' => ['required', 'integer', Rule::exists('unit_of_measures', 'id')],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products')->ignore($this->product->id)],
            'description' => ['nullable', 'string'],
            'product_type_id' => ['required', 'integer', 'exists:product_types,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'unit_of_measure_id' => ['required', 'integer', 'exists:unit_of_measures,id'],
            'vat_rate_id' => ['required', 'integer', 'exists:vat_rates,id'],
            'is_sellable' => ['required', 'boolean'],
            'is_inventoried' => ['required', 'boolean'],
            'selling_price' => ['required_if:is_sellable,true', 'nullable', 'numeric', 'min:0', 'max:999999.99'],
            'default_purchase_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ];
    }
}

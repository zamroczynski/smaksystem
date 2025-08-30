<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHolidayRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'date' => ['required_without_all:day_month,calculation_rule', 'nullable', 'date_format:Y-m-d'],
            'day_month' => ['required_without_all:date,calculation_rule', 'nullable', 'regex:/^(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/'],
            'calculation_rule' => ['required_without_all:date,day_month', 'nullable', 'array'],
            'calculation_rule.base_type' => ['required_with:calculation_rule', 'string', Rule::in(['event', 'holiday'])],
            'calculation_rule.base_event' => ['required_if:calculation_rule.base_type,event', 'nullable', 'string', Rule::in(['easter'])],
            'calculation_rule.base_holiday_id' => ['required_if:calculation_rule.base_type,holiday', 'nullable', 'integer', Rule::exists('holidays', 'id')],
            'calculation_rule.offset' => ['required_with:calculation_rule', 'integer'],
        ];
    }
}

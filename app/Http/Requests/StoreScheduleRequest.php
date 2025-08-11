<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('Edycja Grafików Pracy');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'period_start_date' => [
                'required',
                'date_format:Y-m-d',
            ],
            'status' => [
                'required',
                'string',
                Rule::in(['draft', 'published', 'archived']),
            ],
            'selected_shift_templates' => [
                'nullable',
                'array',
            ],
            'selected_shift_templates.*' => [
                'integer',
                Rule::exists('shift_templates', 'id'),
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nazwa grafiku jest wymagana.',
            'name.string' => 'Nazwa grafiku musi być tekstem.',
            'name.max' => 'Nazwa grafiku nie może przekraczać 255 znaków.',
            'period_start_date.required' => 'Data rozpoczęcia okresu grafiku jest wymagana.',
            'period_start_date.date_format' => 'Data rozpoczęcia musi być w formacie RRRR-MM-DD.',
            'status.required' => 'Status grafiku jest wymagany.',
            'status.string' => 'Status grafiku musi być tekstem.',
            'status.in' => 'Nieprawidłowy status grafiku.',
            'selected_shift_templates.array' => 'Wybrane harmonogramy zmian muszą być w formacie tablicy.',
            'selected_shift_templates.*.integer' => 'Każdy wybrany harmonogram zmiany musi być liczbą całkowitą.',
            'selected_shift_templates.*.exists' => 'Wybrany harmonogram zmiany nie istnieje.',
        ];
    }
}

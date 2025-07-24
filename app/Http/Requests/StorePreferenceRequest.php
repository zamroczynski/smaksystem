<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePreferenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('Preferencje');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date_from' => ['required', 'date', 'after_or_equal:today'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'description' => ['nullable', 'string', 'max:1000'],
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
            'date_from.required' => 'Data rozpoczęcia jest wymagana.',
            'date_from.date' => 'Data rozpoczęcia musi być poprawną datą.',
            'date_from.after_or_equal' => 'Data rozpoczęcia nie może być wcześniejsza niż dzisiaj.',
            'date_to.required' => 'Data zakończenia jest wymagana.',
            'date_to.date' => 'Data zakończenia musi być poprawną datą.',
            'date_to.after_or_equal' => 'Data zakończenia musi być późniejsza lub równa dacie rozpoczęcia.',
            'description.string' => 'Opis musi być tekstem.',
            'description.max' => 'Opis nie może przekraczać :max znaków.',
        ];
    }
}

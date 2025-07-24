<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class StoreShiftTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('Harmonogram Zmian');
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
                Rule::unique('shift_templates')->ignore($this->route('shift_template')),
            ],
            'time_from' => [
                'required',
                'date_format:H:i',
            ],
            'time_to' => [
                'required',
                'date_format:H:i',
            ],
            'duration_hours' => [
                'required',
                'numeric',
                'min:0.01',
                'max:24.0',
            ],
            'required_staff_count' => [ 
                'required',
                'integer',
                'min:0',
            ],
        ];
    }

    /**
     * Sprawdzenie spójności time_from, time_to i duration_hours.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $timeFrom = $this->input('time_from');
            $timeTo   = $this->input('time_to');
            $duration = (float) $this->input('duration_hours');

            try {
                $start = Carbon::today()->setTimeFromTimeString($timeFrom);
                $end   = Carbon::today()->setTimeFromTimeString($timeTo);

                if ($end->lte($start)) {
                    $end->addDay();
                }

                $calculated = $start->floatDiffInHours($end);

                if (abs($calculated - $duration) > 0.005) {
                    $validator->errors()->add(
                        'duration_hours',
                        'Obliczony czas trwania (' . number_format($calculated, 2) . 'h) nie zgadza się z przesłanym czasem trwania (' . number_format($duration, 2) . 'h).'
                    );
                }

            } catch (\Exception $e) {
                $msg = 'Błąd parsowania czasu: ' . $e->getMessage();
                $validator->errors()->add('time_from', $msg);
                $validator->errors()->add('time_to',   $msg);
            }
        });
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nazwa harmonogramu zmian jest wymagana.',
            'name.string' => 'Nazwa harmonogramu zmian musi być tekstem.',
            'name.max' => 'Nazwa harmonogramu zmian nie może przekraczać 255 znaków.',
            'name.unique' => 'Harmonogram zmian o takiej nazwie już istnieje.',
            'time_from.required' => 'Godzina rozpoczęcia jest wymagana.',
            'time_from.date_format' => 'Godzina rozpoczęcia musi być w formacie HH:MM (np. 08:00).',
            'time_to.required' => 'Godzina zakończenia jest wymagana.',
            'time_to.date_format' => 'Godzina zakończenia musi być w formacie HH:MM (np. 16:00).',
            'duration_hours.required' => 'Czas trwania zmiany jest wymagany.',
            'duration_hours.numeric' => 'Czas trwania zmiany musi być liczbą.',
            'duration_hours.min' => 'Czas trwania zmiany musi być większy niż zero.',
            'duration_hours.max' => 'Czas trwania zmiany nie może przekraczać 24 godzin.',
            'required_staff_count.required' => 'Wymagana liczba pracowników jest obowiązkowa.', 
            'required_staff_count.integer' => 'Wymagana liczba pracowników musi być liczbą całkowitą.',
            'required_staff_count.min' => 'Wymagana liczba pracowników nie może być ujemna.',
        ];
    }
}

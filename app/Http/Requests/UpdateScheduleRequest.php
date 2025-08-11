<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScheduleRequest extends FormRequest
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
        $schedule = $this->route('schedule');
        $rules = [
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
            'assignments' => ['nullable', 'array'],
            'assignments.*.shift_template_id' => ['required', 'integer', 'exists:shift_templates,id'],
            'assignments.*.assignment_date' => ['required', 'date_format:Y-m-d'],
            'assignments.*.position' => ['required', 'integer', 'min:1'],
            'assignments.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];

        if ($schedule) {
            $rules['name'][] = Rule::in([$schedule->name]);
            $rules['period_start_date'][] = Rule::in([$schedule->period_start_date->format('Y-m-d')]);
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $assignments = $this->input('assignments');

            if (empty($assignments)) {
                return; // Brak przypisań, nie ma co walidować
            }

            $seenAssignments = []; // Klucz: "user_id_shift_template_id_assignment_date"

            foreach ($assignments as $index => $assignment) {
                // Sprawdzamy tylko, jeśli user_id jest ustawione (nie null)
                if (isset($assignment['user_id']) && $assignment['user_id'] !== null) {
                    $userId = $assignment['user_id'];
                    $shiftTemplateId = $assignment['shift_template_id'];
                    $assignmentDate = $assignment['assignment_date'];

                    // Tworzymy unikalny klucz dla kombinacji użytkownika, zmiany i daty
                    $key = "{$userId}_{$shiftTemplateId}_{$assignmentDate}";

                    if (isset($seenAssignments[$key])) {
                        // Jeśli taka kombinacja już istnieje, dodaj błąd walidacji
                        $validator->errors()->add(
                            "assignments.{$index}.user_id", // Błąd dla bieżącego elementu w tablicy
                            "Użytkownik jest już przypisany do tej samej zmiany w dniu {$assignmentDate}."
                        );
                        // Możesz dodać błąd również do poprzedniego wystąpienia, jeśli chcesz
                        $validator->errors()->add(
                            "assignments.{$seenAssignments[$key]}.user_id",
                            "Użytkownik jest już przypisany do tej samej zmiany w dniu {$assignmentDate}."
                        );
                    } else {
                        $seenAssignments[$key] = $index;
                    }
                }
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
            'name.required' => 'Nazwa grafiku jest wymagana.',
            'name.string' => 'Nazwa grafiku musi być tekstem.',
            'name.max' => 'Nazwa grafiku nie może przekraczać 255 znaków.',

            'period_start_date.required' => 'Data rozpoczęcia okresu grafiku jest wymagana.',
            'period_start_date.date_format' => 'Data rozpoczęcia musi być w formacie RRRR-MM-DD.',
            'period_start_date.in' => 'Nie można zmienić daty rozpoczęcia okresu grafiku po jego utworzeniu.',

            'status.required' => 'Status grafiku jest wymagany.',
            'status.string' => 'Status grafiku musi być tekstem.',
            'status.in' => 'Nieprawidłowy status grafiku.',

            'assignments.array' => 'Przypisania muszą być tablicą.',
            'assignments.*.shift_template_id.required' => 'ID szablonu zmiany jest wymagane dla każdego przypisania.',
            'assignments.*.shift_template_id.integer' => 'ID szablonu zmiany musi być liczbą całkowitą.',
            'assignments.*.shift_template_id.exists' => 'Wybrany szablon zmiany nie istnieje.',
            'assignments.*.assignment_date.required' => 'Data przypisania jest wymagana dla każdego przypisania.',
            'assignments.*.assignment_date.date_format' => 'Data przypisania musi być w formacie RRRR-MM-DD.',
            'assignments.*.position.required' => 'Pozycja jest wymagana dla każdego przypisania.',
            'assignments.*.position.integer' => 'Pozycja musi być liczbą całkowitą.',
            'assignments.*.position.min' => 'Pozycja musi być co najmniej 1.',
            'assignments.*.user_id.integer' => 'ID użytkownika musi być liczbą całkowitą.',
            'assignments.*.user_id.exists' => 'Wybrany użytkownik nie istnieje.',
        ];
    }
}

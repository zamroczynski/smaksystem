<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('Edycja pracowników');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'login' => 'required|string|max:255|unique:users,login', // Login musi być unikalny
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')], // Email może być pusty, ale jeśli jest, to musi być unikalny i poprawny
            'password' => 'required|string|min:8', // Hasło wymagane i min. 8 znaków
            'role_name' => ['nullable', 'string', Rule::exists('roles', 'name')],
        ];
    }
}

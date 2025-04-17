<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserFromRequest extends FormRequest
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
            'first_name' => 'sometimes|string|min:4|max:24',
            'last_name' => 'sometimes|string|min:4|max:24',
            'phone' => 'sometimes|string',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                'string',
                'unique:users'
            ],
        ];
    }
}

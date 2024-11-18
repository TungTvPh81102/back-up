<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends BaseFormRequest
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
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'string|in:admin,member,staff',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'phone' => 'required|nullable|string|max:255',
            'status' => 'required|string|in:active,inactive,banned',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends BaseFormRequest
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
       
        $userId = $this->route('user');

        return [
            'last_name' => 'sometimes|required|string|max:255',
            'first_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $userId,
            'password' => 'sometimes|required|string|min:8',
            'role' => 'sometimes|string|in:admin,member,staff',
            'avatar' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'phone' => 'sometimes|required|nullable|string|max:255,unique:users,phone,' . $userId,
            'status' => 'sometimes|required|string|in:active,inactive,banned',
        ];
    }
}

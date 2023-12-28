<?php

namespace App\Http\Requests\Auth\User;

use Illuminate\Contracts\Validation\Validator as Validation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserUpdateRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . auth()->user()->id,
            'old_password' => 'required',
            'password' => 'sometimes|nullable|confirmed|string|min:8', // Updated password validation
            'phone' => 'required|numeric|unique:users,phone,' . auth()->user()->id,
            'address' => 'required|string',
            'postal_code' => 'required|numeric',
            'city' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
        ];
    }
    public function failedValidation(Validation $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'   => false,
            'message'   => 'Validation errors',
            'errors'      => $validator->errors()
        ], 400));
    }
}

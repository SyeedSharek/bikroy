<?php

namespace App\Http\Requests\Auth\Admin;

use Illuminate\Contracts\Validation\Validator as Validation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AdminProfileUpdateRequest extends FormRequest
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
            'name' => 'required|max:200',
            'email' => 'required|string|email:rfc,dns|max:255|unique:admins,email,' . auth('admin')->user()->id,
            'phone' => 'required|numeric|unique:admins,phone,' . auth('admin')->user()->id,
            'password' => 'sometimes|nullable|confirmed|string|min:3', // Updated password validation
            'image' => 'sometimes|nullable|mimes:jpg,jpeg,png,gif',
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

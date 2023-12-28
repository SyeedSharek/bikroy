<?php

namespace App\Http\Requests\Auth\Admin;

use App\Models\Admin;
use Illuminate\Contracts\Validation\Validator as Validation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserUpdateRequest extends FormRequest
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
    public function rules(Request $user): array
    {
        return [
            'name' => 'sometimes|nullable',
            'email' => [
                'sometimes',
                'nullable',
                'string',
                'email:rfc,dns',
                'max:255',
                Rule::unique('admins')->ignore($user->id),
            ],
            'phone' => [
                'sometimes',
                'nullable',
                'string',
                Rule::unique('admins')->ignore($user->id),
            ],
            'old_password' => 'sometimes|nullable',
            'password' => 'sometimes|nullable|confirmed|string|min:3',
            // 'roles' => 'sometimes' . Rule::unique('admins')->ignore($user->id),
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

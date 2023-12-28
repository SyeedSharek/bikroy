<?php

namespace App\Http\Requests\Plan;

use App\Models\SubscriptionType;
use Illuminate\Contracts\Validation\Validator as Validation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlanUpdateRequest extends FormRequest
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
    public function rules(Request $request): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subscription_types')->ignore($request->id),
            ],
            'price' => 'required|max:5',
            'period_type' => 'required',
            'time_period' => 'required',
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
    public function messages()
    {
        return [
            'price.required' => 'The price field must not be greater than 5 digit.',
        ];
    }
}

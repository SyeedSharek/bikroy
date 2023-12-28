<?php

namespace App\Http\Requests\Area;

use Illuminate\Contracts\Validation\Validator as Validation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AreaStoreRequest extends FormRequest
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

            'location_id' => 'required',
            'name' => 'required|string|unique:areas',
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
            'location_id.required' => 'Location field is required',

        ];
    }
}

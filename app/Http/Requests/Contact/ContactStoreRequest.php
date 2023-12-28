<?php

namespace App\Http\Requests\Contact;

use Illuminate\Contracts\Validation\Validator as Validation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
class ContactStoreRequest extends FormRequest
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
            'name'=>'required',
            'email'=>'required|string|email:rfc,dns|max:255|unique:contacts',
            'phone'=>'required|numeric|unique:contacts',
            'message'=>'required',

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

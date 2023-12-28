<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\Validator as Validation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
class SettingsUpdateRequest extends FormRequest
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
            'name'   => 'required|string',
            'image'  => 'mimes:jpeg,png,jpg,gif|max:2048',
            'header' => '',
            'footer' => '',
            'about'  => '',
            'old_limit' => '',
            'new_limit' => '',
            'terms_conditions' => '',
            'support_policy' => '',
            'privacy_policy' => '',
            'boosting_price' => 'required',
            'boosting_discount_price' => 'required',
            'light_color'=>'',
            'dark_color'=>'',
            'facebook'=>'active_url',
            'instagram'=>'active_url',
            'twitter'=>'active_url',
            'wtsapp'=>'active_url',
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

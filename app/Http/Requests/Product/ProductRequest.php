<?php

namespace App\Http\Requests\Product;

use Illuminate\Contracts\Validation\Validator as Validation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductRequest extends FormRequest
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
            // Validation rules for various fields
            'title' => 'required',
            'brand_id' => 'required',
            'category_id' => 'required',
            'subcategory_id' => 'required',
            'location_id' => 'required',
            'area_id' => 'required',
            'description' => 'required',
            'price' => 'required',
            'images' => 'required|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg',
        ];
    }

    public function messages()
    {
        return [
            // Custom error messages
            'images.max' => 'You can upload maximum :max Images only.',
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

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class MakePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator) {
        throw new HttpResponseException(response()->json($validator->errors()->getMessages(), 422));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'product_ids' => 'required|array',
            'quantities' => 'required|array',
            'quantities.*' => 'integer|min:1', // تأكد أن كل كمية هي رقم صحيح أكبر من 0
        ];
    }

    public function messages(): array
    {
        return [
            'product_ids.required' => 'The Product ids is required.',
            'product_ids.array' => 'The Product ids must be an array.',
            'quantities.required' => 'The quantities is required.',
            'quantities.array' => 'The quantities must be an array.',
            'quantities.*.integer' => 'Each quantity must be an integer.',
            'quantities.*.min' => 'Each quantity must be at least 1.',
        ];
    }
}
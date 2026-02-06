<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CurrencyRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $rules = [
            'code' => 'required|string|max:3|unique:currencies,code',
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];

        // For update, ignore the current currency's code
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['code'] = [
                'required',
                'string',
                'max:3',
                Rule::unique('currencies', 'code')->ignore($this->route('currency'))
            ];
        }

        return $rules;
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Currency code is required',
            'code.unique' => 'Currency code already exists',
            'code.max' => 'Currency code must not exceed 3 characters',
            'name.required' => 'Currency name is required',
            'name.max' => 'Currency name must not exceed 255 characters',
            'symbol.required' => 'Currency symbol is required',
            'symbol.max' => 'Currency symbol must not exceed 10 characters',
            'exchange_rate.required' => 'Exchange rate is required',
            'exchange_rate.numeric' => 'Exchange rate must be a number',
            'exchange_rate.min' => 'Exchange rate must be at least 0',
        ];
    }
}

<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AssignmentSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'content' => 'nullable|string',
            'files' => 'nullable|array',
            'files.*' => 'string',
        ];

        // Additional rules for grading
        if ($this->isMethod('PUT') && $this->has('points_earned')) {
            $rules['points_earned'] = 'required|numeric|min:0';
            $rules['feedback'] = 'nullable|string';
            $rules['inline_comments'] = 'nullable|array';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'points_earned.required' => 'Points earned is required for grading',
            'points_earned.min' => 'Points earned cannot be negative',
        ];
    }
}

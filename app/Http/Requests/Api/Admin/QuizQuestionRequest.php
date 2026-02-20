<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class QuizQuestionRequest extends FormRequest
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
            'type' => 'required|in:single_choice,multiple_choice,true_false,short_answer,long_answer,fill_blank,matching,ordering,image_choice,essay',
            'question' => 'required|string',
            'explanation' => 'nullable|string',
            'points' => 'required|integer|min:1',
            'order' => 'integer|min:0',
            'is_required' => 'boolean',
            'image' => 'nullable|string',
            'audio' => 'nullable|string',
            'video' => 'nullable|string',
            'settings' => 'nullable|array',
            
            // Options validation
            'options' => 'required_if:type,single_choice,multiple_choice,true_false,image_choice,matching,ordering,short_answer,fill_blank|array|min:1',
            'options.*.option_text' => 'required|string',
            'options.*.is_correct' => 'boolean',
            'options.*.feedback' => 'nullable|string',
            'options.*.image' => 'nullable|string',
            'options.*.order' => 'integer|min:0',
            'options.*.match_with' => 'nullable|string',
        ];
    }
}

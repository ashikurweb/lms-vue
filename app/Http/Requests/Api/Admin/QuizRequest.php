<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class QuizRequest extends FormRequest
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
            'course_id' => 'required|exists:courses,id',
            'lesson_id' => 'nullable|exists:lessons,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'max_attempts' => 'required|integer|min:0',
            'show_answers_after_submission' => 'boolean',
            'show_correct_answers' => 'boolean',
            'randomize_questions' => 'boolean',
            'randomize_options' => 'boolean',
            'questions_per_page' => 'required|integer|min:0',
            'allow_review' => 'boolean',
            'is_required' => 'boolean',
            'is_published' => 'boolean',
            'order' => 'integer|min:0',
            'meta' => 'nullable|array',
        ];
    }
}

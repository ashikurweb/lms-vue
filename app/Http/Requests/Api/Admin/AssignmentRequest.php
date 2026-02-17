<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => 'required|exists:courses,id',
            'lesson_id' => 'nullable|exists:lessons,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'total_points' => 'required|integer|min:1|max:1000',
            'passing_points' => 'required|integer|min:0|lte:total_points',
            'due_date' => 'nullable|date|after:now',
            'allow_late_submission' => 'boolean',
            'late_submission_penalty' => 'integer|min:0|max:100',
            'max_file_size' => 'integer|min:1|max:100',
            'allowed_file_types' => 'nullable|array',
            'allowed_file_types.*' => 'string',
            'max_submissions' => 'integer|min:1|max:10',
            'is_required' => 'boolean',
            'is_published' => 'boolean',
            'order' => 'integer|min:0',
            'attachments' => 'nullable|array',
            'attachments.*' => 'string',
            'meta' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'Please select a course',
            'course_id.exists' => 'Selected course does not exist',
            'lesson_id.exists' => 'Selected lesson does not exist',
            'title.required' => 'Assignment title is required',
            'total_points.required' => 'Total points is required',
            'total_points.min' => 'Total points must be at least 1',
            'passing_points.required' => 'Passing points is required',
            'passing_points.lte' => 'Passing points cannot exceed total points',
            'due_date.after' => 'Due date must be in the future',
            'late_submission_penalty.max' => 'Late submission penalty cannot exceed 100%',
            'max_file_size.max' => 'Maximum file size cannot exceed 100MB',
        ];
    }
}

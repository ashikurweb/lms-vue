<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DiscussionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:question,discussion,announcement',
            'status' => 'required|in:open,answered,closed,flagged',
            'is_pinned' => 'boolean',
            'is_featured' => 'boolean',
            'course_id' => 'required|exists:courses,id',
            'lesson_id' => 'nullable|exists:lessons,id',
            'user_id' => 'required|exists:users,id',
            'meta' => 'nullable|array',
        ];
    }
}

<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseRequest extends FormRequest
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
        $id = $this->course ? $this->course->id : null;

        return [
            'instructor_id' => ['required', 'exists:users,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('courses')->ignore($id)],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'price_type' => ['required', Rule::in(['free', 'paid', 'subscription'])],
            'price' => ['required_if:price_type,paid', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'min:0'],
            'level' => ['required', Rule::in(['beginner', 'intermediate', 'advanced', 'all_levels'])],
            'language' => ['required', 'string', 'max:10'],
            'is_published' => ['boolean'],
            'is_featured' => ['boolean'],
            'thumbnail' => ['nullable', 'string', 'max:500'],
            'promo_video' => ['nullable', 'string', 'max:500'],
            'promo_video_type' => ['nullable', Rule::in(['upload', 'external', 'bunny'])],
            'status' => ['required', Rule::in(['draft', 'pending_review', 'published', 'unpublished', 'rejected'])],
        ];
    }
}

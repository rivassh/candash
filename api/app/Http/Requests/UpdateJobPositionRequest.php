<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobPositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'department' => ['sometimes', 'string', 'max:255'],
            'level' => ['sometimes', 'in:junior,mid,senior'],
            'employment_type' => ['sometimes', 'in:full_time,part_time,contract,internship'],
            'min_experience_years' => ['sometimes', 'integer', 'min:0', 'max:50'],
            'education_requirements' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:draft,open,closed,archived'],
            'required_skills' => ['sometimes', 'array'],
            'preferred_skills' => ['sometimes', 'array'],
        ];
    }
}
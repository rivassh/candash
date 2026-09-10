<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCandidateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'summary' => ['nullable', 'string'],
            'experiences' => ['sometimes', 'array'],
            'experiences.*.company' => ['required_with:experiences', 'string'],
            'experiences.*.job_title' => ['required_with:experiences', 'string'],
            'experiences.*.start_date' => ['required_with:experiences', 'date'],
            'experiences.*.end_date' => ['nullable', 'date'],
            'experiences.*.is_current' => ['boolean'],
            'educations' => ['sometimes', 'array'],
            'educations.*.degree' => ['required_with:educations', 'string'],
            'educations.*.field_of_study' => ['required_with:educations', 'string'],
            'educations.*.institution' => ['required_with:educations', 'string'],
            'educations.*.graduation_year' => ['nullable', 'integer'],
        ];
    }
}
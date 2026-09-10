<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\JobLevel;
use App\Enums\EmploymentType;
use App\Enums\JobPositionStatus;

class StoreJobPositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'level' => ['required', 'in:'.implode(',', JobLevel::values())],
            'employment_type' => ['required', 'in:full_time,part_time,contract,internship'],
            'min_experience_years' => ['integer', 'min:0', 'max:50'],
            'education_requirements' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'external_id' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:draft,open,closed,archived'],
            'required_skills' => ['array'],
            'required_skills.*.name' => ['required_with:required_skills', 'string', 'max:255'],
            'required_skills.*.weight' => ['integer', 'min:1', 'max:10'],
            'required_skills.*.min_years' => ['integer', 'min:0', 'max:50'],
            'preferred_skills' => ['array'],
            'preferred_skills.*' => ['string', 'max:255'],
        ];
    }
}
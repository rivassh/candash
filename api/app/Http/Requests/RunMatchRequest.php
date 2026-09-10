<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RunMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'candidate_ids' => ['nullable', 'array'],
            'candidate_ids.*' => ['integer', 'exists:candidates,id'],
            'job_position_id' => ['nullable', 'integer', 'exists:job_positions,id'],
            'position_ids' => ['nullable', 'array'],
            'position_ids.*' => ['integer', 'exists:job_positions,id'],
        ];
    }
}
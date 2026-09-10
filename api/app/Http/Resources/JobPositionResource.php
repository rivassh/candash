<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JobPositionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'department' => $this->department,
            'level' => $this->level?->value,
            'level_label' => $this->level?->label(),
            'employment_type' => $this->employment_type?->value,
            'employment_type_label' => $this->employment_type?->label(),
            'min_experience_years' => $this->min_experience_years,
            'education_requirements' => $this->education_requirements,
            'description' => $this->description,
            'external_id' => $this->external_id,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'required_skills' => $this->required_skills ?? [],
            'preferred_skills' => $this->preferred_skills ?? [],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
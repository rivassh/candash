<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CandidateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'linkedin_url' => $this->linkedin_url,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'summary' => $this->summary,
            'enrichment_data' => $this->enrichment_data,
            'skills' => $this->whenLoaded('skills', fn() =>
                $this->skills->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'category' => $s->category,
                    'years_experience' => $s->pivot->years_experience ?? 0,
                    'confidence' => $s->pivot->confidence ?? 1.0,
                ])
            ),
            'experiences' => $this->whenLoaded('experiences', fn() =>
                $this->experiences->map(fn($e) => [
                    'id' => $e->id,
                    'company' => $e->company,
                    'job_title' => $e->job_title,
                    'start_date' => $e->start_date,
                    'end_date' => $e->end_date,
                    'is_current' => $e->is_current,
                    'duration_years' => $e->getDurationYears(),
                    'responsibilities' => $e->responsibilities,
                    'confidence' => $e->confidence,
                    'source' => $e->source,
                ])
            ),
            'educations' => $this->whenLoaded('educations', fn() =>
                $this->educations->map(fn($e) => [
                    'id' => $e->id,
                    'degree' => $e->degree,
                    'field_of_study' => $e->field_of_study,
                    'institution' => $e->institution,
                    'graduation_year' => $e->graduation_year,
                    'confidence' => $e->confidence,
                    'source' => $e->source,
                ])
            ),
            'latest_resume' => $this->whenLoaded('latestResume', fn() => [
                'id' => $this->latestResume->id,
                'status' => $this->latestResume->status?->value,
                'confidence' => $this->latestResume->confidence,
                'created_at' => $this->latestResume->created_at,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
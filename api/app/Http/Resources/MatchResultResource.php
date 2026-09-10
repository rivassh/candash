<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MatchResultResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'candidate_id' => $this->candidate_id,
            'job_position_id' => $this->job_position_id,
            'total_score' => $this->total_score,
            'breakdown' => $this->breakdown,
            'strengths' => $this->strengths ?? [],
            'gaps' => $this->gaps ?? [],
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'notes' => $this->notes,
            'candidate' => $this->whenLoaded('candidate', fn() => [
                'id' => $this->candidate->id,
                'name' => $this->candidate->name,
                'email' => $this->candidate->email,
            ]),
            'job_position' => $this->whenLoaded('jobPosition', fn() => [
                'id' => $this->jobPosition->id,
                'title' => $this->jobPosition->title,
                'department' => $this->jobPosition->department,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
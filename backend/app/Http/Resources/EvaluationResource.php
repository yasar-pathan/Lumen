<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message_id' => $this->message_id,
            'reviewer_name' => $this->reviewer_name,
            'rating' => $this->rating,
            'flag' => $this->flag,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
        ];
    }
}

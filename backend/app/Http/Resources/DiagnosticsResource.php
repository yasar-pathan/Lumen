<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiagnosticsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message_id' => $this->message_id,
            'retrieval_relevance_avg' => $this->retrieval_relevance_avg,
            'groundedness_score' => $this->groundedness_score,
            'root_cause' => $this->root_cause,
            'suggested_fix' => $this->suggested_fix,
            'missing_terms' => $this->missing_terms,
            'latency_ms' => $this->latency_ms,
            'safety_flag' => $this->safety_flag,
            'provider_name' => $this->provider_name,
            'created_at' => $this->created_at,
            'message' => new MessageResource($this->whenLoaded('message')),
            'conversation' => $this->relationLoaded('message') && $this->message->relationLoaded('conversation') ? $this->message->conversation : null,
        ];
    }
}

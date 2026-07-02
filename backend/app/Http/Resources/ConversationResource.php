<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'prompt_version_id' => $this->prompt_version_id,
            'title' => $this->title,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'prompt_version' => new PromptVersionResource($this->whenLoaded('promptVersion')),
        ];
    }
}

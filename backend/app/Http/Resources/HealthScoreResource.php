<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HealthScoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'score' => $this['score'],
            'breakdown' => $this['breakdown'],
        ];
    }
}

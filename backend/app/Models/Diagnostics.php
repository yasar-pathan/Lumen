<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diagnostics extends Model
{
    protected $fillable = [
        'message_id',
        'retrieval_relevance_avg',
        'groundedness_score',
        'root_cause',
        'suggested_fix',
        'missing_terms',
        'latency_ms',
        'safety_flag',
        'provider_name',
    ];

    protected $casts = [
        'missing_terms' => 'array',
        'safety_flag' => 'boolean',
        'retrieval_relevance_avg' => 'float',
        'groundedness_score' => 'float',
        'latency_ms' => 'integer',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}

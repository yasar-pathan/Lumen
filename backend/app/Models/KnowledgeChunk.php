<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeChunk extends Model
{
    protected $fillable = ['title', 'content', 'tags'];

    protected $casts = [
        'tags' => 'array',
    ];
}

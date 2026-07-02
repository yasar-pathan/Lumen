<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromptVersion extends Model
{
    protected $fillable = ['name', 'system_prompt', 'version', 'status'];

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}

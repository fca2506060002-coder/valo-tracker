<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValoMatch extends Model
{
    protected $fillable = [
        'user_id',
        'played_at',
        'result',
        'rr_change',
        'screenshot_path',
    ];

    protected $casts = [
        'played_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
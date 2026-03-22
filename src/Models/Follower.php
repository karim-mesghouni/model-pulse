<?php

namespace Karim\ModelPulse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Follower extends Model
{
    protected $table = 'model_pulse_followers';

    protected $fillable = [
        'followable_id',
        'followable_type',
        'follower_id',
        'follower_type',
    ];

    protected $casts = [
        'followed_at' => 'datetime',
    ];

    public function followable(): MorphTo
    {
        return $this->morphTo();
    }
    public function follower(): MorphTo
    {
        return $this->morphTo();
    }


}

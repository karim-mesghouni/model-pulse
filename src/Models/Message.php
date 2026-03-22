<?php

namespace Karim\ModelPulse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class Message extends Model
{
    protected $table = 'model_pulse_messages';

    protected $fillable = [
        'activity_type_id',
        'messageable_type',
        'messageable_id',
        'type',
        'name',
        'subject',
        'body',
        'summary',
        'is_internal',
        'date_deadline',
        'pinned_at',
        'log_name',
        'event',
        'assignable_type',
        'assignable_id',
        'causer_type',
        'causer_id',
        'properties',
    ];

    protected $casts = [
        'properties'    => 'array',
        'date_deadline' => 'date',
    ];

    public function messageable(): MorphTo
    {
        return $this->morphTo();
    }



    public function activityType()
    {
        return $this->belongsTo(ActivityType::class, 'activity_type_id');
    }

    public function causer()
    {
        return $this->morphTo();
    }

    public function assignable()
    {
        return $this->morphTo();
    }


    public function setPropertiesAttribute($value)
    {
        $this->attributes['properties'] = json_encode($value);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($data) {
            $user = Auth::user();
            $data->causer_type ??= $user instanceof Model ? $user->getMorphClass() : null;
            $data->causer_id ??= $user?->id;
        });

        static::updating(function ($data) {
            $user = Auth::user();
            $data->causer_type = $user instanceof Model ? $user->getMorphClass() : null;
            $data->causer_id = $user?->id;
        });
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'message_id');
    }
}

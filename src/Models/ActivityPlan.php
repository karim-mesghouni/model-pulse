<?php

namespace Karim\ModelPulse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;


class ActivityPlan extends Model
{
    use   SoftDeletes;

    protected $table = 'model_pulse_activity_plans';

    protected $fillable = [
        'plugin',
        'creator_id',
        "creator_type",
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];



    public function creator(): MorphTo
    {
        return $this->morphTo();
    }

    public function activityTypes(): HasMany
    {
        return $this->hasMany(ActivityType::class, 'activity_plan_id');
    }

    public function activityPlanTemplates(): HasMany
    {
        return $this->hasMany(ActivityPlanTemplate::class, 'plan_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($activityPlan) {
            $authUser = Auth::user();

            $activityPlan->creator_id ??= $authUser?->id;
            $activityPlan->creator_type ??= $authUser instanceof Model ? $authUser->getMorphClass() : null;
        });
    }
}

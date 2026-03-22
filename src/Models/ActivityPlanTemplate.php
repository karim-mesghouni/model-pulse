<?php

namespace Karim\ModelPulse\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class ActivityPlanTemplate extends Model implements Sortable
{
    use HasFactory, SortableTrait;

    protected $table = 'model_pulse_activity_plan_templates';

    protected $fillable = [
        'sort',
        'plan_id',
        'activity_type_id',
        'responsible_id',
        'creator_id',
        "creator_type",
        'delay_count',
        'delay_unit',
        'delay_from',
        'summary',
        'responsible_type',
        'note',
    ];

    public $sortable = [
        'order_column_name'  => 'sort',
        'sort_when_creating' => true,
    ];

    public function activityPlan(): BelongsTo
    {
        return $this->belongsTo(ActivityPlan::class, 'plan_id');
    }

    public function activityType(): BelongsTo
    {
        return $this->belongsTo(ActivityType::class, 'activity_type_id');
    }

    public function responsible(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($activityPlanTemplate) {
            $authUser = Auth::user();
            $activityPlanTemplate->creator_id ??= $authUser?->id;
            $activityPlanTemplate->creator_type ??= $authUser instanceof Model ? $authUser->getMorphClass() : null;
        });
    }
}

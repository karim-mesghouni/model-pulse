<?php

namespace Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Karim\ModelPulse\Traits\Followable;
use Karim\ModelPulse\Traits\HasLogActivity;
use Karim\ModelPulse\Traits\Messagable;
use Karim\ModelPulse\Traits\Attachable;

class TestProject extends Model
{
    use Followable;
    use HasLogActivity;
    use Messagable;
    use Attachable;
    use SoftDeletes;

    protected $table = 'test_projects';

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(TestUser::class, 'owner_id');
    }

    public function getModelTitle(): string
    {
        return $this->name ?? 'Project';
    }

    public function getLogAttributeLabels(): array
    {
        return [
            'name' => 'Name',
            'status' => 'Status',
            'owner.name' => 'Owner',
            'meta' => 'Meta',
        ];
    }
}

<?php

use Illuminate\Support\Facades\DB;
use Karim\ModelPulse\Models\ActivityPlan;
use Karim\ModelPulse\Models\ActivityType;

it('sets creator fields on activity plan creation', function () {
    $user = test()->actingAs();

    $plan = ActivityPlan::query()->create([
        'name' => 'Plan A',
        'is_active' => true,
    ]);

    expect($plan->creator_id)->toBe($user->id);
    expect($plan->creator_type)->toBe($user->getMorphClass());
});

it('loads activity type plan and suggested type relationships', function () {
    $user = test()->actingAs();

    $plan = ActivityPlan::query()->create([
        'name' => 'Plan A',
        'is_active' => true,
        'creator_type' => $user->getMorphClass(),
        'creator_id' => $user->id,
    ]);

    $typeA = ActivityType::query()->create([
        'name' => 'Type A',
        'delay_unit' => 'day',
        'delay_from' => 'current',
        'activity_plan_id' => $plan->id,
        'creator_type' => $user->getMorphClass(),
        'creator_id' => $user->id,
    ]);

    $typeB = ActivityType::query()->create([
        'name' => 'Type B',
        'delay_unit' => 'day',
        'delay_from' => 'current',
        'activity_plan_id' => $plan->id,
        'creator_type' => $user->getMorphClass(),
        'creator_id' => $user->id,
    ]);

    DB::table('model_pulse_activity_type_suggestions')->insert([
        'activity_type_id' => $typeA->id,
        'suggested_activity_type_id' => $typeB->id,
    ]);

    expect($typeA->activityPlan->id)->toBe($plan->id);
    expect($typeA->suggestedActivityTypes->pluck('id')->all())->toContain($typeB->id);
});

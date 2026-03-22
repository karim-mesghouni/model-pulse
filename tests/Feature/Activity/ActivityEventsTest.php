<?php

use Karim\ModelPulse\Events\ActivityLogged;
use Tests\Fixtures\Models\TestProject;

it('dispatches activity logged event with activity message payload', function () {
    $user = test()->actingAs();
    $project = TestProject::query()->create([
        'name' => 'Activity',
        'status' => 'draft',
        'owner_id' => $user->id,
    ]);
    $project->name = 'Activity Updated';

    $dispatched = [];
    app('events')->listen(ActivityLogged::class, function ($event) use (&$dispatched) {
        $dispatched[] = $event;
    });

    $message = $project->logModelActivity('updated');

    expect($dispatched)->toHaveCount(1);
    expect($dispatched[0]->model->is($project))->toBeTrue();
    expect($dispatched[0]->event)->toBe('updated');
    expect($dispatched[0]->activityMessage->is($message))->toBeTrue();
});

it('does not dispatch activity logged event when updated has no dirty changes', function () {
    test()->actingAs();
    $project = TestProject::query()->create([
        'name' => 'Activity',
    ]);

    $dispatched = [];
    app('events')->listen(ActivityLogged::class, function ($event) use (&$dispatched) {
        $dispatched[] = $event;
    });

    $result = $project->logModelActivity('updated');

    expect($result)->toBeNull();
    expect($dispatched)->toHaveCount(0);
});

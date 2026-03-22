<?php

use Karim\ModelPulse\Models\Message;
use Tests\Fixtures\Models\TestProject;

it('creates an audit message when model is created', function () {
    $user = test()->actingAs();

    $project = TestProject::query()->create([
        'name' => 'Initial',
        'status' => 'draft',
        'owner_id' => $user->id,
        'meta' => ['a' => 1],
    ]);

    $log = $project->logModelActivity('created');

    expect($log)->not->toBeNull();
    expect($log->event)->toBe('created');
    expect($log->causer_id)->toBe($user->id);
});

it('logs change history when model attributes are updated', function () {
    $user = test()->actingAs();
    $project = TestProject::query()->create([
        'name' => 'Initial',
        'status' => 'draft',
        'owner_id' => $user->id,
        'meta' => ['a' => 1, 'b' => 2],
    ]);

    $project->name = 'Renamed';
    $project->meta = ['b' => 2, 'a' => 1, 'c' => 3];
    $updateLog = $project->logModelActivity('updated');

    expect($updateLog)->not->toBeNull();
    expect($updateLog->properties['Name']['new_value'])->toBe('Renamed');
});

it('records soft delete and restore audit trail events', function () {
    $user = test()->actingAs();
    $project = TestProject::query()->create([
        'name' => 'Initial',
        'status' => 'draft',
        'owner_id' => $user->id,
    ]);

    $project->logModelActivity('soft_deleted');
    $project->logModelActivity('restored');

    $events = Message::query()
        ->where('messageable_id', $project->id)
        ->pluck('event')
        ->all();

    expect($events)->toContain('soft_deleted');
    expect($events)->toContain('restored');
});

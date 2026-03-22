<?php

use Karim\ModelPulse\Models\Message;
use Tests\Fixtures\Models\TestProject;

it('casts properties as array', function () {
    $user = test()->actingAs();
    $project = TestProject::query()->create(['name' => 'P1', 'owner_id' => $user->id]);

    $message = Message::query()->create([
        'messageable_type' => $project->getMorphClass(),
        'messageable_id' => $project->id,
        'assignable_type' => $user->getMorphClass(),
        'assignable_id' => $user->id,
        'causer_type' => $user->getMorphClass(),
        'causer_id' => $user->id,
        'type' => 'note',
        'subject' => 'Subject',
        'properties' => ['alpha' => 1],
    ]);

    expect($message->properties)->toBeArray();
    expect($message->properties['alpha'])->toBe(1);
});

it('persists provided causer fields correctly', function () {
    $user = test()->actingAs();
    $project = TestProject::query()->create(['name' => 'P1', 'owner_id' => $user->id]);

    $message = Message::query()->create([
        'messageable_type' => $project->getMorphClass(),
        'messageable_id' => $project->id,
        'assignable_type' => $user->getMorphClass(),
        'assignable_id' => $user->id,
        'causer_type' => $user->getMorphClass(),
        'causer_id' => $user->id,
        'type' => 'note',
    ]);

    expect($message->causer_id)->toBe($user->id);
    expect($message->causer_type)->toBe($user->getMorphClass());
});

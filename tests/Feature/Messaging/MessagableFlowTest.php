<?php

use Karim\ModelPulse\Models\Message;
use Tests\Fixtures\Models\TestProject;

it('creates and removes a message', function () {
    $user = test()->actingAs();
    $project = TestProject::query()->create(['name' => 'P1', 'owner_id' => $user->id]);

    $message = $project->addMessage([
        'type' => 'note',
        'subject' => 'Subject',
        'body' => 'Body',
    ]);

    expect($message)->toBeInstanceOf(Message::class);
    expect($message->messageable_id)->toBe($project->id);
    expect($message->causer_id)->toBe($user->id);

    expect($project->removeMessage($message->id))->toBeTrue();
    expect(Message::query()->count())->toBe(0);
});

it('marks unread messages as read', function () {
    test()->actingAs();
    $project = TestProject::query()->create(['name' => 'P1']);

    $project->addMessage(['type' => 'note', 'subject' => 'S1']);
    $project->addMessage(['type' => 'note', 'subject' => 'S2']);

    expect($project->unRead())->toHaveCount(2);
    expect($project->markAsRead())->toBe(2);
    expect($project->unRead())->toHaveCount(0);
});

it('pins and unpins messages', function () {
    test()->actingAs();
    $project = TestProject::query()->create(['name' => 'P1']);

    $message = $project->addMessage(['type' => 'note', 'subject' => 'S1']);

    expect($project->pinMessage($message))->toBeTrue();
    expect($project->getPinnedMessages())->toHaveCount(1);

    expect($project->unpinMessage($message))->toBeTrue();
    expect($project->getPinnedMessages())->toHaveCount(0);
});

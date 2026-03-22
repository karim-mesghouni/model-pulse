<?php

use Karim\ModelPulse\Events\MessageCreated;
use Karim\ModelPulse\Events\MessagePinned;
use Karim\ModelPulse\Events\MessageRemoved;
use Karim\ModelPulse\Events\MessageReplied;
use Karim\ModelPulse\Events\MessagesMarkedRead;
use Karim\ModelPulse\Events\MessageUnpinned;
use Tests\Fixtures\Models\TestProject;

it('dispatches message created event with payload', function () {
    test()->actingAs();
    $project = TestProject::query()->create(['name' => 'Events']);

    $dispatched = [];
    app('events')->listen(MessageCreated::class, function ($event) use (&$dispatched) {
        $dispatched[] = $event;
    });

    $message = $project->addMessage(['type' => 'note', 'subject' => 'S1']);

    expect($dispatched)->toHaveCount(1);
    expect($dispatched[0]->messageable->is($project))->toBeTrue();
    expect($dispatched[0]->message->is($message))->toBeTrue();
});

it('dispatches message replied event with parent and reply payload', function () {
    test()->actingAs();
    $project = TestProject::query()->create(['name' => 'Events']);
    $parent = $project->addMessage(['type' => 'note', 'subject' => 'Parent']);

    $dispatched = [];
    app('events')->listen(MessageReplied::class, function ($event) use (&$dispatched) {
        $dispatched[] = $event;
    });

    $reply = $project->replyToMessage($parent, ['type' => 'note', 'subject' => 'Reply']);

    expect($dispatched)->toHaveCount(1);
    expect($dispatched[0]->messageable->is($project))->toBeTrue();
    expect($dispatched[0]->parentMessage->is($parent))->toBeTrue();
    expect($dispatched[0]->replyMessage->is($reply))->toBeTrue();
});

it('dispatches message removed event only on successful delete', function () {
    test()->actingAs();
    $project = TestProject::query()->create(['name' => 'Events']);
    $message = $project->addMessage(['type' => 'note', 'subject' => 'Delete']);

    $dispatched = [];
    app('events')->listen(MessageRemoved::class, function ($event) use (&$dispatched) {
        $dispatched[] = $event;
    });

    expect($project->removeMessage($message->id))->toBeTrue();

    expect($dispatched)->toHaveCount(1);
});

it('does not dispatch message removed event on missing message', function () {
    test()->actingAs();
    $project = TestProject::query()->create(['name' => 'Events']);

    $dispatched = [];
    app('events')->listen(MessageRemoved::class, function ($event) use (&$dispatched) {
        $dispatched[] = $event;
    });

    expect($project->removeMessage(999999))->toBeFalse();

    expect($dispatched)->toHaveCount(0);
});

it('dispatches messages marked read event only when rows are affected', function () {
    test()->actingAs();
    $project = TestProject::query()->create(['name' => 'Events']);
    $project->addMessage(['type' => 'note', 'subject' => 'S1']);

    $dispatched = [];
    app('events')->listen(MessagesMarkedRead::class, function ($event) use (&$dispatched) {
        $dispatched[] = $event;
    });

    $affectedRows = $project->markAsRead();

    expect($dispatched)->toHaveCount(1);
    expect($dispatched[0]->messageable->is($project))->toBeTrue();
    expect($dispatched[0]->affectedRows)->toBe($affectedRows);
});

it('does not dispatch messages marked read event when nothing changes', function () {
    test()->actingAs();
    $project = TestProject::query()->create(['name' => 'Events']);

    $dispatched = [];
    app('events')->listen(MessagesMarkedRead::class, function ($event) use (&$dispatched) {
        $dispatched[] = $event;
    });

    expect($project->markAsRead())->toBe(0);

    expect($dispatched)->toHaveCount(0);
});

it('dispatches pinned and unpinned events on successful state changes', function () {
    test()->actingAs();
    $project = TestProject::query()->create(['name' => 'Events']);
    $message = $project->addMessage(['type' => 'note', 'subject' => 'Pin']);

    $pinnedEvents = [];
    $unpinnedEvents = [];
    app('events')->listen(MessagePinned::class, function ($event) use (&$pinnedEvents) {
        $pinnedEvents[] = $event;
    });
    app('events')->listen(MessageUnpinned::class, function ($event) use (&$unpinnedEvents) {
        $unpinnedEvents[] = $event;
    });

    expect($project->pinMessage($message))->toBeTrue();
    expect($project->unpinMessage($message))->toBeTrue();

    expect($pinnedEvents)->toHaveCount(1);
    expect($unpinnedEvents)->toHaveCount(1);
});

it('does not dispatch pin event for foreign message', function () {
    test()->actingAs();
    $owner = TestProject::query()->create(['name' => 'Owner']);
    $other = TestProject::query()->create(['name' => 'Other']);
    $foreignMessage = $other->addMessage(['type' => 'note', 'subject' => 'Foreign']);

    $dispatched = [];
    app('events')->listen(MessagePinned::class, function ($event) use (&$dispatched) {
        $dispatched[] = $event;
    });

    expect($owner->pinMessage($foreignMessage))->toBeFalse();

    expect($dispatched)->toHaveCount(0);
});

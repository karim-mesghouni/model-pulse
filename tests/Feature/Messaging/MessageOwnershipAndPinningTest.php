<?php

use Tests\Fixtures\Models\TestProject;

it('prevents pinning message from another model', function () {
    test()->actingAs();
    $owner = TestProject::query()->create(['name' => 'Owner']);
    $other = TestProject::query()->create(['name' => 'Other']);

    $message = $other->addMessage(['type' => 'note', 'subject' => 'S1']);

    expect($owner->pinMessage($message))->toBeFalse();
});

it('returns false when removing missing message id', function () {
    test()->actingAs();
    $project = TestProject::query()->create(['name' => 'Owner']);

    expect($project->removeMessage(999999))->toBeFalse();
});

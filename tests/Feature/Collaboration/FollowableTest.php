<?php

use Karim\ModelPulse\Models\Follower;
use Tests\Fixtures\Models\TestProject;
use Tests\Fixtures\Models\TestUser;

it('adds followers idempotently', function () {
    $project = TestProject::query()->create(['name' => 'P1']);
    $user = TestUser::query()->create(['name' => 'Follower']);

    $first = $project->addFollower($user);
    $second = $project->addFollower($user);

    expect($first->id)->toBe($second->id);
    expect($project->followers()->count())->toBe(1);
    expect($project->isFollowedBy($user))->toBeTrue();
});

it('removes followers correctly', function () {
    $project = TestProject::query()->create(['name' => 'P1']);
    $user = TestUser::query()->create(['name' => 'Follower']);

    $project->addFollower($user);
    $removed = $project->removeFollower($user);

    expect($removed)->toBeTrue();
    expect($project->isFollowedBy($user))->toBeFalse();
    expect(Follower::query()->count())->toBe(0);
});

it('returns false when removing absent follower', function () {
    $project = TestProject::query()->create(['name' => 'P1']);
    $user = TestUser::query()->create(['name' => 'Follower']);

    expect($project->removeFollower($user))->toBeFalse();
    expect($project->isFollowedBy($user))->toBeFalse();
});

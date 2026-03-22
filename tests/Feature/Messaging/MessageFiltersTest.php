<?php

use Illuminate\Support\Carbon;
use Tests\Fixtures\Models\TestProject;

it('filters messages by type and internal flag', function () {
    test()->actingAs();
    $project = TestProject::query()->create(['name' => 'P1']);

    $project->addMessage(['type' => 'note', 'subject' => 'N1', 'is_internal' => false]);
    $project->addMessage(['type' => 'email', 'subject' => 'E1', 'is_internal' => true]);

    $filtered = $project->withFilters([
        'type' => ['email'],
        'is_internal' => true,
    ]);

    expect($filtered)->toHaveCount(1);
    expect($filtered->first()->type)->toBe('email');
});

it('filters messages by search text', function () {
    test()->actingAs();
    $project = TestProject::query()->create(['name' => 'P1']);

    $project->addMessage(['type' => 'note', 'subject' => 'Roadmap', 'body' => 'Q1 planning']);
    $project->addMessage(['type' => 'note', 'subject' => 'Retrospective', 'body' => 'Release review']);

    $filtered = $project->withFilters(['search' => 'Road']);

    expect($filtered)->toHaveCount(1);
    expect($filtered->first()->subject)->toBe('Roadmap');
});

it('returns messages by date deadline range', function () {
    test()->actingAs();
    $project = TestProject::query()->create(['name' => 'P1']);

    $project->addMessage([
        'type' => 'note',
        'subject' => 'Past',
        'date_deadline' => Carbon::parse('2026-01-01'),
    ]);
    $project->addMessage([
        'type' => 'note',
        'subject' => 'InRange',
        'date_deadline' => Carbon::parse('2026-02-10'),
    ]);

    $results = $project->getMessagesByDateRange(
        Carbon::parse('2026-02-01'),
        Carbon::parse('2026-02-28')
    );

    expect($results)->toHaveCount(1);
    expect($results->first()->subject)->toBe('InRange');
});

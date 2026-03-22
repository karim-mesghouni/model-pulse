<?php

use Illuminate\Support\Facades\DB;
use Tests\Fixtures\Models\TestProject;

it('keeps message filter query count within baseline', function () {
    test()->actingAs();
    $project = TestProject::query()->create(['name' => 'Perf']);

    foreach (range(1, 40) as $index) {
        $project->addMessage([
            'type' => $index % 2 === 0 ? 'email' : 'note',
            'subject' => 'Subject '.$index,
            'body' => 'Body '.$index,
            'is_internal' => $index % 3 === 0,
        ]);
    }

    DB::flushQueryLog();
    DB::enableQueryLog();

    $result = $project->withFilters([
        'type' => ['note'],
        'is_internal' => true,
        'search' => 'Subject',
    ]);

    $queryCount = count(DB::getQueryLog());

    expect($result->count())->toBeGreaterThan(0);
    expect($queryCount)->toBeLessThanOrEqual(2);
});

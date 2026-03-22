<?php

use Tests\Fixtures\Models\TestProject;

it('keeps mark-as-read runtime stable across repeated runs', function () {
    test()->actingAs();
    $project = TestProject::query()->create(['name' => 'Runtime']);

    foreach (range(1, 120) as $index) {
        $project->addMessage([
            'type' => 'note',
            'subject' => 'Item '.$index,
            'body' => 'Body',
        ]);
    }

    $firstStart = hrtime(true);
    $project->markAsRead();
    $firstDuration = hrtime(true) - $firstStart;

    $project->messages()->update(['is_read' => false]);

    $secondStart = hrtime(true);
    $project->markAsRead();
    $secondDuration = hrtime(true) - $secondStart;

    expect($firstDuration)->toBeGreaterThan(0);
    expect($secondDuration)->toBeLessThanOrEqual((int) ($firstDuration * 3));
});

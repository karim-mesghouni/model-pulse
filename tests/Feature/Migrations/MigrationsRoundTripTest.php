<?php

use Illuminate\Support\Facades\Schema;

it('runs migration down and up without schema drift', function () {
    $migrationFiles = glob(dirname(__DIR__, 3).'/database/migrations/*.php') ?: [];
    sort($migrationFiles);

    $instances = [];
    foreach ($migrationFiles as $file) {
        $instances[] = require $file;
    }

    foreach (array_reverse($instances) as $migration) {
        $migration->down();
    }

    expect(Schema::hasTable('model_pulse_activity_type_suggestions'))->toBeFalse();
    expect(Schema::hasTable('model_pulse_activity_plan_templates'))->toBeFalse();
    expect(Schema::hasTable('model_pulse_messages'))->toBeFalse();

    foreach ($instances as $migration) {
        $migration->up();
    }

    expect(Schema::hasTable('model_pulse_activity_type_suggestions'))->toBeTrue();
    expect(Schema::hasTable('model_pulse_activity_plan_templates'))->toBeTrue();
    expect(Schema::hasTable('model_pulse_messages'))->toBeTrue();
});

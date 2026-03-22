<?php

use ReflectionMethod;
use Tests\Fixtures\Models\TestProject;

it('formats boolean values as readable strings', function () {
    $project = new TestProject();
    $method = new ReflectionMethod(TestProject::class, 'formatAttributeValue');
    $method->setAccessible(true);

    expect($method->invoke($project, 'flag', true))->toBe('Yes');
    expect($method->invoke($project, 'flag', false))->toBe('No');
});

it('normalizes json arrays with stable key ordering', function () {
    $project = new TestProject();
    $method = new ReflectionMethod(TestProject::class, 'formatAttributeValue');
    $method->setAccessible(true);

    $result = $method->invoke($project, 'meta', '{"b":2,"a":1}');

    expect($result)->toBe(['a' => 1, 'b' => 2]);
});

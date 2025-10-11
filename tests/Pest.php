<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Sleep;
use Tests\TestCase;

uses(TestCase::class)
    ->in('Feature', 'Unit');

uses(RefreshDatabase::class)
    ->in('Feature');

beforeEach(function (): void {
    Str::createRandomStringsNormally();
    Str::createUuidsNormally();
    Http::preventStrayRequests();
    Sleep::fake();

    $this->freezeTime();
});

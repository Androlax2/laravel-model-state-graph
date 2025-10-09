<?php

namespace Androlax\LaravelModelStateGraph\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Androlax\LaravelModelStateGraph\LaravelModelStateGraphServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelModelStateGraphServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
    }
}

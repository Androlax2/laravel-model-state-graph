<?php

declare(strict_types = 1);

namespace Tests;

use Androlax\LaravelModelStateGraph\LaravelModelStateGraphServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        Factory::guessFactoryNamesUsing(
            fn (string $modelName): string => sprintf('Tests\\Fixtures\\Factories\\%sFactory', class_basename($modelName))
        );
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelModelStateGraphServiceProvider::class,
        ];
    }

    protected function setUpDatabase(Application $app): void
    {
        $app['db']->connection()->getSchemaBuilder()->create('test_models', function (Blueprint $table): void {
            $table->increments('id');
            $table->timestamps();
            $table->integer("quantity")->nullable();
            $table->integer("price")->nullable();
            $table->string("status")->nullable();
            $table->string("category")->nullable();
        });
    }
}

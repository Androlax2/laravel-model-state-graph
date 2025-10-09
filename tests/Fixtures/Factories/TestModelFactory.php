<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Tests\Fixtures\Factories;

use Androlax\LaravelModelStateGraph\Tests\Fixtures\Models\TestModel;
use Illuminate\Database\Eloquent\Factories\Factory;

final class TestModelFactory extends Factory
{
    protected $model = TestModel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
        ];
    }
}

<?php

declare(strict_types=1);

use Androlax\LaravelModelStateGraph\Contracts\BusinessRule;
use Androlax\LaravelModelStateGraph\Contracts\FieldRuleSet;
use Androlax\LaravelModelStateGraph\Exceptions\BusinessRuleViolationException;
use Androlax\LaravelModelStateGraph\ModelStateGraph;
use Illuminate\Database\Eloquent\Model;
use Tests\Fixtures\Models\TestModel;

it('can validate model with changes successfully', function () {
    $model = TestModel::create(['quantity' => 10]);
    $model->fill(['quantity' => 15]);

    $graph = ModelStateGraph::for(TestModel::class)
        ->addFieldRuleSet(
            new class implements FieldRuleSet
            {
                public function getField(): string
                {
                    return 'quantity';
                }

                public function getRules(): array
                {
                    return [
                        new class implements BusinessRule
                        {
                            public function supports(Model $model): bool
                            {
                                return $model->isDirty('quantity');
                            }

                            public function validate(Model $model): void
                            {
                                // Simple validation that always passes for positive numbers
                                if ($model->quantity < 0) {
                                    throw new BusinessRuleViolationException('Negative quantity');
                                }
                            }
                        },
                    ];
                }

                public function supports(Model $model): bool
                {
                    return $model->isDirty('quantity');
                }
            }
        );

    expect($graph->isValid($model))->toBeTrue()
        ->and($graph->getViolations($model))->toBeEmpty();
});

it('can detect validation violations', function () {
    $model = TestModel::create(['quantity' => 10]);
    $model->fill(['quantity' => -5]); // Invalid value

    $graph = ModelStateGraph::for(TestModel::class)
        ->addFieldRuleSet(
            new class implements FieldRuleSet
            {
                public function getField(): string
                {
                    return 'quantity';
                }

                public function getRules(): array
                {
                    return [
                        new class implements BusinessRule
                        {
                            public function supports(Model $model): bool
                            {
                                return $model->isDirty('quantity');
                            }

                            public function validate(Model $model): void
                            {
                                if ($model->quantity < 0) {
                                    throw new BusinessRuleViolationException('Quantity cannot be negative');
                                }
                            }
                        },
                    ];
                }

                public function supports(Model $model): bool
                {
                    return $model->isDirty('quantity');
                }
            }
        );

    expect($graph->isValid($model))->toBeFalse()
        ->and($graph->getViolations($model))->not->toBeEmpty();
});

it('skips validation when field rule set does not support model', function () {
    $model = TestModel::create(['quantity' => 10]);
    $model->fill(['price' => 150]); // Only price changed, not quantity

    $graph = ModelStateGraph::for(TestModel::class)
        ->addFieldRuleSet(
            new class implements FieldRuleSet
            {
                public function getField(): string
                {
                    return 'quantity';
                }

                public function getRules(): array
                {
                    return [
                        new class implements BusinessRule
                        {
                            public function supports(Model $model): bool
                            {
                                return true;
                            }

                            public function validate(Model $model): void
                            {
                                throw new BusinessRuleViolationException('This should not run');
                            }
                        },
                    ];
                }

                public function supports(Model $model): bool
                {
                    // Only support if quantity changed
                    return $model->isDirty('quantity');
                }
            }
        );

    // Should pass because quantity rule set doesn't support this model state
    expect($graph->isValid($model))->toBeTrue()
        ->and($graph->getViolations($model))->toBeEmpty();
});

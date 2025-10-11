<?php

declare(strict_types=1);

use Androlax\LaravelModelStateGraph\Contracts\BusinessRule;
use Androlax\LaravelModelStateGraph\Contracts\FieldRuleSet;
use Androlax\LaravelModelStateGraph\Exceptions\BusinessRuleViolationException;
use Androlax\LaravelModelStateGraph\ModelStateGraph;
use Illuminate\Database\Eloquent\Model;
use Tests\Fixtures\Models\TestModel;

it('returns validation result with violations', function () {
    $model = TestModel::create(['quantity' => 10]);
    $model->fill(['quantity' => -5]);

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
                                throw new BusinessRuleViolationException('Test violation message');
                            }
                        },
                    ];
                }

                public function supports(Model $model): bool
                {
                    return true;
                }
            }
        );

    $result = $graph->validate($model);

    expect($result->isValid())->toBeFalse()
        ->and($result->getViolations())->toHaveCount(1)
        ->and($result->getViolations()[0])->toBe('Test violation message');
});

it('returns empty violations when validation passes', function () {
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
                                return true;
                            }

                            public function validate(Model $model): void
                            {
                                // No exception thrown - validation passes
                            }
                        },
                    ];
                }

                public function supports(Model $model): bool
                {
                    return true;
                }
            }
        );

    $result = $graph->validate($model);

    expect($result->isValid())->toBeTrue()
        ->and($result->getViolations())->toBeEmpty();
});

it('aggregates multiple violations', function () {
    $model = TestModel::create(['quantity' => 10, 'price' => 100]);
    $model->fill(['quantity' => -5, 'price' => -10]);

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
                                throw new BusinessRuleViolationException('Invalid quantity');
                            }
                        },
                    ];
                }

                public function supports(Model $model): bool
                {
                    return true;
                }
            }
        )
        ->addFieldRuleSet(
            new class implements FieldRuleSet
            {
                public function getField(): string
                {
                    return 'price';
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
                                throw new BusinessRuleViolationException('Invalid price');
                            }
                        },
                    ];
                }

                public function supports(Model $model): bool
                {
                    return true;
                }
            }
        );

    $result = $graph->validate($model);

    expect($result->isValid())->toBeFalse()
        ->and($result->getViolations())->toHaveCount(2)
        ->and($result->getViolations())->toContain('Invalid quantity', 'Invalid price');
});

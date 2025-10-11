<?php

declare(strict_types=1);

use Androlax\LaravelModelStateGraph\Contracts\BusinessRule;
use Androlax\LaravelModelStateGraph\Contracts\FieldRuleSet;
use Androlax\LaravelModelStateGraph\Exceptions\BusinessRuleViolationException;
use Androlax\LaravelModelStateGraph\ModelStateGraph;
use Illuminate\Database\Eloquent\Model;
use Tests\Fixtures\Models\TestModel;

it('efficiently skips rules that do not support model state', function () {
    $executionCount = 0;

    $rule = new class($executionCount) implements BusinessRule
    {
        public function __construct(private int &$executionCount) {}

        public function supports(Model $model): bool
        {
            return $model->quantity > 50; // Only support high quantities
        }

        public function validate(Model $model): void
        {
            $this->executionCount++;
            // Validation logic here
        }
    };

    $ruleSet = new class($rule) implements FieldRuleSet
    {
        public function __construct(private BusinessRule $rule) {}

        public function getField(): string
        {
            return 'quantity';
        }

        public function getRules(): array
        {
            return [$this->rule];
        }

        public function supports(Model $model): bool
        {
            return true;
        }
    };

    $graph = ModelStateGraph::for(TestModel::class)
        ->addFieldRuleSet($ruleSet);

    $lowQtyModel = TestModel::create(['quantity' => 30]);
    $highQtyModel = TestModel::create(['quantity' => 60]);

    // Should not execute validate() for low quantity
    $graph->isValid($lowQtyModel);
    expect($executionCount)->toBe(0);

    // Should execute validate() for high quantity
    $graph->isValid($highQtyModel);
    expect($executionCount)->toBe(1);
});

it('combines field rule set and business rule supports conditions', function () {
    $model = TestModel::create(['quantity' => 10, 'status' => 'draft']);
    $model->fill(['quantity' => 25]);

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
                                return $model->quantity > 20; // Only for quantities > 20
                            }

                            public function validate(Model $model): void
                            {
                                throw new BusinessRuleViolationException('High quantity rule');
                            }
                        },
                        new class implements BusinessRule
                        {
                            public function supports(Model $model): bool
                            {
                                return $model->status === 'published'; // Only for published
                            }

                            public function validate(Model $model): void
                            {
                                throw new BusinessRuleViolationException('Published rule');
                            }
                        },
                    ];
                }

                public function supports(Model $model): bool
                {
                    return $model->isDirty('quantity'); // Only if quantity changed
                }
            }
        );

    // Only the first rule should execute (quantity > 20)
    $result = $graph->validate($model);

    expect($result->isValid())->toBeFalse()
        ->and($result->getViolations())->toHaveCount(1)
        ->and($result->getViolations()[0])->toBe('High quantity rule');
});

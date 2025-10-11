<?php

declare(strict_types=1);

use Androlax\LaravelModelStateGraph\Contracts\FieldRuleSet;
use Androlax\LaravelModelStateGraph\Exceptions\DuplicateFieldException;
use Androlax\LaravelModelStateGraph\Exceptions\FieldNotFoundException;
use Androlax\LaravelModelStateGraph\ModelStateGraph;
use Illuminate\Database\Eloquent\Model;
use Tests\Fixtures\Models\TestModel;

it('can create graph for a model class', function () {
    $graph = ModelStateGraph::for(TestModel::class);

    expect($graph)->toBeInstanceOf(ModelStateGraph::class);
});

it('can register field rule sets', function () {
    $ruleSet = new class implements FieldRuleSet
    {
        public function getField(): string
        {
            return 'quantity';
        }

        public function getRules(): array
        {
            return [];
        }

        public function supports(Model $model): bool
        {
            return true;
        }
    };

    $graph = ModelStateGraph::for(TestModel::class)
        ->addFieldRuleSet($ruleSet);

    expect($graph)->toBeInstanceOf(ModelStateGraph::class);
});

it('can chain multiple field rule sets', function () {
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
                    return [];
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
                    return [];
                }

                public function supports(Model $model): bool
                {
                    return true;
                }
            }
        );

    expect($graph)->toBeInstanceOf(ModelStateGraph::class);
});

it('throws exception when adding non-rule-set object', function () {
    $invalidObject = new class
    {
        // This doesn't implement FieldRuleSet
    };

    $graph = ModelStateGraph::for(TestModel::class);

    expect(fn () => $graph->addFieldRuleSet($invalidObject))
        ->toThrow(TypeError::class);
});

it('throws exception when adding duplicate field rule set instance', function () {
    $ruleSet = new class implements FieldRuleSet
    {
        public function getField(): string
        {
            return 'quantity';
        }

        public function getRules(): array
        {
            return [];
        }

        public function supports(Model $model): bool
        {
            return true;
        }
    };

    $graph = ModelStateGraph::for(TestModel::class)
        ->addFieldRuleSet($ruleSet);

    expect(fn () => $graph->addFieldRuleSet($ruleSet))
        ->toThrow(DuplicateFieldException::class, 'quantity');
});

it('throws exception when adding rule set for field that is already registered', function () {
    $quantityRuleSet1 = new class implements FieldRuleSet
    {
        public function getField(): string
        {
            return 'quantity';
        }

        public function getRules(): array
        {
            return [];
        }

        public function supports(Model $model): bool
        {
            return true;
        }
    };

    $quantityRuleSet2 = new class implements FieldRuleSet
    {
        public function getField(): string
        {
            return 'quantity';
        }

        public function getRules(): array
        {
            return [];
        }

        public function supports(Model $model): bool
        {
            return true;
        }
    };

    $graph = ModelStateGraph::for(TestModel::class)
        ->addFieldRuleSet($quantityRuleSet1);

    expect(fn () => $graph->addFieldRuleSet($quantityRuleSet2))
        ->toThrow(DuplicateFieldException::class, 'quantity');
});

it('throws exception when adding rule set for field that is not part of the model', function () {
    $ruleSet = new class implements FieldRuleSet
    {
        public function getField(): string
        {
            return 'non_existent_field';
        }

        public function getRules(): array
        {
            return [];
        }

        public function supports(Model $model): bool
        {
            return true;
        }
    };

    $graph = ModelStateGraph::for(TestModel::class);

    expect(fn () => $graph->addFieldRuleSet($ruleSet))
        ->toThrow(FieldNotFoundException::class);
});

it('allows different rule sets for different fields', function () {
    $quantityRuleSet = new class implements FieldRuleSet
    {
        public function getField(): string
        {
            return 'quantity';
        }

        public function getRules(): array
        {
            return [];
        }

        public function supports(Model $model): bool
        {
            return true;
        }
    };

    $priceRuleSet = new class implements FieldRuleSet
    {
        public function getField(): string
        {
            return 'price';
        }

        public function getRules(): array
        {
            return [];
        }

        public function supports(Model $model): bool
        {
            return true;
        }
    };

    $statusRuleSet = new class implements FieldRuleSet
    {
        public function getField(): string
        {
            return 'status';
        }

        public function getRules(): array
        {
            return [];
        }

        public function supports(Model $model): bool
        {
            return true;
        }
    };

    $graph = ModelStateGraph::for(TestModel::class)
        ->addFieldRuleSet($quantityRuleSet)
        ->addFieldRuleSet($priceRuleSet)
        ->addFieldRuleSet($statusRuleSet);

    expect($graph)->toBeInstanceOf(ModelStateGraph::class);
});

it('validates model fields exist during construction', function () {
    // TestModel has: quantity, price, status, etc.
    $graph = ModelStateGraph::for(TestModel::class);

    // Should not throw for valid fields
    $graph->addFieldRuleSet(
        new class implements FieldRuleSet
        {
            public function getField(): string
            {
                return 'quantity';
            }

            public function getRules(): array
            {
                return [];
            }

            public function supports(Model $model): bool
            {
                return true;
            }
        }
    );

    $graph->addFieldRuleSet(
        new class implements FieldRuleSet
        {
            public function getField(): string
            {
                return 'price';
            }

            public function getRules(): array
            {
                return [];
            }

            public function supports(Model $model): bool
            {
                return true;
            }
        }
    );

    $graph->addFieldRuleSet(
        new class implements FieldRuleSet
        {
            public function getField(): string
            {
                return 'status';
            }

            public function getRules(): array
            {
                return [];
            }

            public function supports(Model $model): bool
            {
                return true;
            }
        }
    );

    $graph->addFieldRuleSet(
        new class implements FieldRuleSet
        {
            public function getField(): string
            {
                return 'category';
            }

            public function getRules(): array
            {
                return [];
            }

            public function supports(Model $model): bool
            {
                return true;
            }
        }
    );

    expect($graph)->toBeInstanceOf(ModelStateGraph::class);
});

it('handles field validation case insensitively', function () {
    $ruleSet = new class implements FieldRuleSet
    {
        public function getField(): string
        {
            return 'Quantity';
        } // Different case

        public function getRules(): array
        {
            return [];
        }

        public function supports(Model $model): bool
        {
            return true;
        }
    };

    $graph = ModelStateGraph::for(TestModel::class);

    expect(fn () => $graph->addFieldRuleSet($ruleSet))
        ->toThrow(FieldNotFoundException::class);
});

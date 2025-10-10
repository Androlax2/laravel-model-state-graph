<?php

declare(strict_types=1);

use Androlax\LaravelModelStateGraph\Contexts\TransitionContext;
use Androlax\LaravelModelStateGraph\Exceptions\FieldModificationException;
use Androlax\LaravelModelStateGraph\FieldRulesGraph;
use Androlax\LaravelModelStateGraph\Specification\FieldSpecification;
use Tests\Fixtures\Models\TestModel;
use Tests\Fixtures\Specifications\NameFieldSpecification;
use Tests\Fixtures\Specifications\QuantityFieldSpecification;

it('executes state transition on single model', function (): void {
    $graph = FieldRulesGraph::for(TestModel::class)
        ->addSpecification(new QuantityFieldSpecification())
        ->addSpecification(new NameFieldSpecification());

    $model = TestModel::create(['quantity' => 5, 'name' => 'Old Name']);
    $model = $model->fresh();

    $model->quantity = 10;
    $model->name = 'New Name';

    $graph->executeStateTransition($model);

    expect($model->quantity)->toBe(10)
        ->and($model->name)->toBe('New Name');
});

it('throws exception when field modification is not allowed', function (): void {
    $denyingSpec = new class extends FieldSpecification
    {
        public function canModify(TransitionContext $context): bool
        {
            return false;
        }

        public function getFieldName(): string
        {
            return 'quantity';
        }
    };

    $graph = FieldRulesGraph::for(TestModel::class)
        ->addSpecification($denyingSpec);

    $model = TestModel::create(['quantity' => 5]);
    $model = $model->fresh();
    $model->quantity = 10;

    expect(fn () => $graph->executeStateTransition($model))
        ->toThrow(FieldModificationException::class);
});

it('only processes changed fields between states', function (): void {
    $processedFields = [];

    $trackingQuantitySpec = new class($processedFields) extends FieldSpecification
    {
        private array $processedFields;

        public function __construct(array &$processedFields)
        {
            $this->processedFields = &$processedFields;
        }

        public function canModify(TransitionContext $context): bool
        {
            $this->processedFields[] = 'quantity';

            return true;
        }

        public function getFieldName(): string
        {
            return 'quantity';
        }
    };

    $trackingNameSpec = new class($processedFields) extends FieldSpecification
    {
        private array $processedFields;

        public function __construct(array &$processedFields)
        {
            $this->processedFields = &$processedFields;
        }

        public function canModify(TransitionContext $context): bool
        {
            $this->processedFields[] = 'name';

            return true;
        }

        public function getFieldName(): string
        {
            return 'name';
        }
    };

    $graph = FieldRulesGraph::for(TestModel::class)
        ->addSpecification($trackingQuantitySpec)
        ->addSpecification($trackingNameSpec);

    $model = TestModel::create([
        'quantity' => 5,
        'name' => 'Same Name',
        'status' => 1,
    ]);
    $model = $model->fresh();

    $model->quantity = 10;

    $graph->executeStateTransition($model);

    expect($processedFields)->toBe(['quantity'])
        ->and($model->quantity)->toBe(10)
        ->and($model->name)->toBe('Same Name');
});

it('processes multiple changed fields with specifications', function (): void {
    $processedFields = [];

    $trackingQuantitySpec = new class($processedFields) extends FieldSpecification
    {
        private array $processedFields;

        public function __construct(array &$processedFields)
        {
            $this->processedFields = &$processedFields;
        }

        public function canModify(TransitionContext $context): bool
        {
            $this->processedFields[] = 'quantity';

            return true;
        }

        public function getFieldName(): string
        {
            return 'quantity';
        }
    };

    $trackingNameSpec = new class($processedFields) extends FieldSpecification
    {
        private array $processedFields;

        public function __construct(array &$processedFields)
        {
            $this->processedFields = &$processedFields;
        }

        public function canModify(TransitionContext $context): bool
        {
            $this->processedFields[] = 'name';

            return true;
        }

        public function getFieldName(): string
        {
            return 'name';
        }
    };

    $graph = FieldRulesGraph::for(TestModel::class)
        ->addSpecification($trackingQuantitySpec)
        ->addSpecification($trackingNameSpec);

    $model = TestModel::create([
        'quantity' => 5,
        'name' => 'Old Name',
    ]);
    $model = $model->fresh();

    $model->quantity = 15;
    $model->name = 'New Name';

    $graph->executeStateTransition($model);

    expect($processedFields)->toContain('quantity')
        ->and($processedFields)->toContain('name')
        ->and($processedFields)->toHaveCount(2)
        ->and($model->quantity)->toBe(15)
        ->and($model->name)->toBe('New Name');
});

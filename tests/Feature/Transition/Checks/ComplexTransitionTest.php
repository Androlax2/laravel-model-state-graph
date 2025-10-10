<?php

declare(strict_types=1);

use Androlax\LaravelModelStateGraph\Contexts\TransitionContext;
use Androlax\LaravelModelStateGraph\FieldRulesGraph;
use Androlax\LaravelModelStateGraph\Specification\FieldSpecification;
use Tests\Fixtures\Models\TestModel;

it('handles complex multi-field transitions', function (): void {
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
        'price' => 100,
        'category' => 'A',
    ]);
    $model = $model->fresh();

    $model->quantity = 25;
    $model->name = 'New Name';
    $model->price = 150;
    $model->category = 'B';

    $graph->executeStateTransition($model);

    expect($processedFields)->toContain('quantity')
        ->and($processedFields)->toContain('name')
        ->and($processedFields)->not->toContain('price')
        ->and($processedFields)->not->toContain('category')
        ->and($model->quantity)->toBe(25)
        ->and($model->name)->toBe('New Name')
        ->and($model->price)->toBe(150)
        ->and($model->category)->toBe('B');
});

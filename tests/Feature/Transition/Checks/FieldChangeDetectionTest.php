<?php

declare(strict_types=1);

use Androlax\LaravelModelStateGraph\Contexts\TransitionContext;
use Androlax\LaravelModelStateGraph\FieldRulesGraph;
use Androlax\LaravelModelStateGraph\Specification\FieldSpecification;
use Tests\Fixtures\Models\TestModel;
use Tests\Fixtures\Specifications\NameFieldSpecification;
use Tests\Fixtures\Specifications\QuantityFieldSpecification;

it('detects and processes only modified fields', function (): void {
    $processedFields = [];

    $trackingSpec = new class($processedFields) extends FieldSpecification
    {
        private array $processedFields;

        public function __construct(array &$processedFields)
        {
            $this->processedFields = &$processedFields;
        }

        public function canModify(TransitionContext $context): bool
        {
            $this->processedFields[] = $context->field();

            return true;
        }

        public function getFieldName(): string
        {
            return 'quantity';
        }
    };

    $graph = FieldRulesGraph::for(TestModel::class)
        ->addSpecification($trackingSpec);

    $model = TestModel::create([
        'quantity' => 5,
        'name' => 'Name',
        'status' => 'active',
    ]);

    $model = $model->fresh();
    $model->quantity = 8;

    $graph->executeStateTransition($model);

    expect($processedFields)->toContain('quantity')
        ->and($processedFields)->not->toContain('name')
        ->and($processedFields)->not->toContain('status');
});

it('handles multiple changed fields with different specifications', function (): void {
    $graph = FieldRulesGraph::for(TestModel::class)
        ->addSpecification(new QuantityFieldSpecification())
        ->addSpecification(new NameFieldSpecification());

    $model = TestModel::create([
        'quantity' => 5,
        'name' => 'Old Name',
        'description' => 'Same description',
    ]);

    $model = $model->fresh();
    $model->quantity = 15;
    $model->name = 'New Name';

    $graph->executeStateTransition($model);

    expect($model->quantity)->toBe(15)
        ->and($model->name)->toBe('New Name')
        ->and($model->description)->toBe('Same description');
});

<?php

declare(strict_types=1);

use Androlax\LaravelModelStateGraph\Contexts\TransitionContext;
use Androlax\LaravelModelStateGraph\FieldRulesGraph;
use Androlax\LaravelModelStateGraph\Specification\FieldSpecification;
use Tests\Fixtures\Models\TestModel;

it('creates proper TransitionContext for each changed field', function (): void {
    $receivedContexts = [];

    $trackingSpec = new class($receivedContexts) extends FieldSpecification
    {
        private array $receivedContexts;

        public function __construct(array &$receivedContexts)
        {
            $this->receivedContexts = &$receivedContexts;
        }

        public function canModify(TransitionContext $context): bool
        {
            $this->receivedContexts[] = [
                'field' => $context->field(),
                'old' => $context->oldValue(),
                'new' => $context->newValue(),
                'entity' => $context->model(),
                'is_dirty' => $context->isDirty(),
                'changed_fields' => $context->changedFields(),
            ];

            return true;
        }

        public function getFieldName(): string
        {
            return 'quantity';
        }
    };

    $graph = FieldRulesGraph::for(TestModel::class)
        ->addSpecification($trackingSpec);

    $model = TestModel::create(['quantity' => 5]);

    $model = $model->fresh();
    $model->quantity = 8;

    $graph->executeStateTransition($model);

    expect($receivedContexts)->toHaveCount(1)
        ->and($receivedContexts[0]['field'])->toBe('quantity')
        ->and($receivedContexts[0]['old'])->toBe(5)
        ->and($receivedContexts[0]['new'])->toBe(8)
        ->and($receivedContexts[0]['entity'])->toBe($model)
        ->and($receivedContexts[0]['is_dirty'])->toBeTrue()
        ->and($receivedContexts[0]['changed_fields'])->toContain('quantity');
});

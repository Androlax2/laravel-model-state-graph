<?php

declare(strict_types=1);

use Androlax\LaravelModelStateGraph\Contracts\ValidationResult;
use Androlax\LaravelModelStateGraph\ModelStateGraph;
use Tests\Fixtures\Models\TestModel;
use Tests\Fixtures\ModelStateGraph\TestModel\FieldSpecifications\NameFieldSpecificationWithRequiredValidator;

it('validates field changes against specifications', function (): void {
    $model = TestModel::factory()->create(['name' => 'Old Name']);
    $oldModel = clone $model;

    $model->name = 'New Name';

    $graph = (new ModelStateGraph(TestModel::class))
        ->withSpecification(new NameFieldSpecificationWithRequiredValidator());

    $result = $graph->validate($model, $oldModel);

    expect($result)->toBeInstanceOf(ValidationResult::class)
        ->and($result->isValid())->toBeTrue();
});

it('collects violations when validation fails', function (): void {
    $model = TestModel::factory()->create(['name' => 'Old Name']);
    $oldModel = clone $model;

    $model->name = ''; // Empty name should fail RequiredValidator

    $graph = (new ModelStateGraph(TestModel::class))
        ->withSpecification(new NameFieldSpecificationWithRequiredValidator());

    $result = $graph->validate($model, $oldModel);

    expect($result->isValid())->toBeFalse()
        ->and($result->getViolations()->hasViolations())->toBeTrue()
        ->and($result->getViolations()->getForField('name'))->toHaveCount(1);

    $nameViolations = $result->getViolations()->getForField('name');
    expect($nameViolations[0]->getMessage())->toBe('Field is required');
});

it('returns valid result when no fields changed', function (): void {
    $model = TestModel::factory()->create(['name' => 'Same Name']);
    $oldModel = clone $model;

    $graph = (new ModelStateGraph(TestModel::class))
        ->withSpecification(new NameFieldSpecificationWithRequiredValidator());

    $result = $graph->validate($model, $oldModel);

    expect($result->isValid())->toBeTrue();
});

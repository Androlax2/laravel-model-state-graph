<?php

declare(strict_types=1);

use Androlax\LaravelModelStateGraph\Exceptions\FieldNotFoundException;
use Androlax\LaravelModelStateGraph\ModelStateGraph;
use Tests\Fixtures\Models\TestModel;
use Tests\Fixtures\ModelStateGraph\TestModel\FieldSpecifications\NameFieldSpecificationWithRequiredValidator;
use Tests\Fixtures\ModelStateGraph\TestModel\FieldSpecifications\UnknownFieldSpecification;

beforeEach(function (): void {
    $this->model = TestModel::factory()->create();
    $this->oldModel = clone $this->model;
});

it('throws field not found exception for unknown fields during validation', function (): void {
    $modelStateGraph = new ModelStateGraph(TestModel::class);

    $modelStateGraph->withSpecification(new UnknownFieldSpecification());

    $modelStateGraph->validate($this->model, $this->oldModel);
})->throws(
    FieldNotFoundException::class,
    "Field 'unknown_field' does not exist on model"
);

it('provides helpful suggestions in the exception', function (): void {
    $modelStateGraph = new ModelStateGraph(TestModel::class);

    $modelStateGraph->withSpecification(new UnknownFieldSpecification());

    try {
        $modelStateGraph->validate($this->model, $this->oldModel);
        $this->fail('Expected FieldNotFoundException was not thrown');
    } catch (FieldNotFoundException $e) {
        expect($e->getMessage())->toContain('unknown_field')
            ->and($e->getMessage())->toContain(TestModel::class)
            ->and($e->getField())->toBe('unknown_field')
            ->and($e->getModelClass())->toBe(TestModel::class);
    }
});

it('allows valid field specifications', function (): void {
    $modelStateGraph = new ModelStateGraph(TestModel::class);

    $modelStateGraph->withSpecification(
        new NameFieldSpecificationWithRequiredValidator()
    );

    $result = $modelStateGraph->validate($this->model, $this->oldModel);

    expect($modelStateGraph)->toBeInstanceOf(ModelStateGraph::class)
        ->and($result->isValid())->toBeTrue();
});

it('validates multiple specifications and fails on first unknown field during validation', function (): void {
    $modelStateGraph = new ModelStateGraph(TestModel::class);

    $modelStateGraph->withSpecification(
        new NameFieldSpecificationWithRequiredValidator()
    );

    $modelStateGraph->withSpecification(new UnknownFieldSpecification());

    $modelStateGraph->validate($this->model, $this->oldModel);
})->throws(FieldNotFoundException::class);

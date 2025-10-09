<?php

declare(strict_types=1);

use Androlax\LaravelModelStateGraph\Exceptions\FieldNotFoundException;
use Androlax\LaravelModelStateGraph\ModelStateGraph;
use Androlax\LaravelModelStateGraph\Tests\Fixtures\FieldSpecifications\TestModel\NameFieldSpecification;
use Androlax\LaravelModelStateGraph\Tests\Fixtures\FieldSpecifications\TestModel\UnknownFieldSpecification;
use Androlax\LaravelModelStateGraph\Tests\Fixtures\Models\TestModel;

beforeEach(function () {
    $this->model = TestModel::factory()->create();
});

it('throws field not found exception for unknown fields', function () {
    $modelStateGraph = new ModelStateGraph($this->model);

    $modelStateGraph->withSpecification(new UnknownFieldSpecification());
})->throws(
    FieldNotFoundException::class,
    "Field 'unknown_field' does not exist on model"
);

it('provides helpful suggestions in the exception', function () {
    $modelStateGraph = new ModelStateGraph($this->model);

    try {
        $modelStateGraph->withSpecification(new UnknownFieldSpecification());
        $this->fail('Expected FieldNotFoundException was not thrown');
    } catch (FieldNotFoundException $e) {
        expect($e->getMessage())->toContain('unknown_field')
            ->and($e->getMessage())->toContain(TestModel::class)
            ->and($e->getField())->toBe('unknown_field')
            ->and($e->getModelClass())->toBe(TestModel::class);
    }
});

it('allows valid field specifications', function () {
    $modelStateGraph = new ModelStateGraph($this->model);

    // This should not throw for valid fields
    $modelStateGraph->withSpecification(
        new NameFieldSpecification()
    );

    expect($modelStateGraph)->toBeInstanceOf(ModelStateGraph::class);
});

it('validates multiple specifications and fails on first unknown field', function () {
    $modelStateGraph = new ModelStateGraph($this->model);

    $modelStateGraph->withSpecification(
        new NameFieldSpecification()
    );

    // This should throw when adding the unknown field specification
    $modelStateGraph->withSpecification(new UnknownFieldSpecification());
})->throws(FieldNotFoundException::class);

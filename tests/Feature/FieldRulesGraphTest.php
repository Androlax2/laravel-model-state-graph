<?php

declare(strict_types=1);

use Androlax\LaravelModelStateGraph\Exceptions\DuplicateSpecificationException;
use Androlax\LaravelModelStateGraph\Exceptions\FieldNotFoundException;
use Androlax\LaravelModelStateGraph\FieldRulesGraph;
use Androlax\LaravelModelStateGraph\Specification\FieldSpecification;
use Illuminate\Database\Eloquent\Model;
use Tests\Fixtures\Models\TestModel;
use Tests\Fixtures\Specifications\HeightFieldSpecification;
use Tests\Fixtures\Specifications\MaximumQuantityFieldSpecification;
use Tests\Fixtures\Specifications\NameFieldSpecification;
use Tests\Fixtures\Specifications\OtherNameFieldSpecification;
use Tests\Fixtures\Specifications\QuantityFieldSpecification;

it('throws exception when creating rules graph with constructor for non-model class', function () {
    final class Foo {}

    expect(fn () => new FieldRulesGraph(Foo::class))
        ->toThrow(InvalidArgumentException::class);
});

it('throws exception when creating rules graph with for() method for non-model class', function () {
    final class Bar {}

    expect(fn () => FieldRulesGraph::for(Bar::class))
        ->toThrow(InvalidArgumentException::class);
});

it('successfully creates rules graph for model class', function () {
    $modelClass = new class extends Model {};

    $graph = new FieldRulesGraph($modelClass::class);
    expect($graph->getEntityClass())->toBe($modelClass::class);

    $graphFor = FieldRulesGraph::for($modelClass::class);
    expect($graphFor->getEntityClass())->toBe($modelClass::class);
});

it('successfully creates rules graph for eloquent model', function () {
    $graph = new FieldRulesGraph(TestModel::class);
    expect($graph->getEntityClass())->toBe(TestModel::class);

    $graphFor = FieldRulesGraph::for(TestModel::class);
    expect($graphFor->getEntityClass())->toBe(TestModel::class);
});

it('can add field specification', function () {
    $graph = FieldRulesGraph::for(TestModel::class)
        ->addSpecification(new NameFieldSpecification());

    expect($graph->getRegistry()->getSpecification('name'))->toBeInstanceOf(NameFieldSpecification::class);
});

it('can add multiple field specifications', function () {
    $graph = FieldRulesGraph::for(TestModel::class)
        ->addSpecification(new NameFieldSpecification())
        ->addSpecification(new QuantityFieldSpecification())
        ->addSpecifications([
            new HeightFieldSpecification(),
            new MaximumQuantityFieldSpecification(),
        ]);

    expect($graph->getRegistry()->getSpecification('name'))->toBeInstanceOf(NameFieldSpecification::class)
        ->and($graph->getRegistry()->getSpecification('quantity'))->toBeInstanceOf(
            QuantityFieldSpecification::class
        )
        ->and($graph->getRegistry()->getSpecification('height'))->toBeInstanceOf(
            HeightFieldSpecification::class
        )
        ->and($graph->getRegistry()->getSpecification('maximum_quantity'))->toBeInstanceOf(
            MaximumQuantityFieldSpecification::class
        );
});

it('throws exception when adding non-specification object', function () {
    $graph = FieldRulesGraph::for(TestModel::class);

    expect(fn () => $graph->addSpecification(new stdClass()))
        ->toThrow(TypeError::class);
});

it('throws exception when adding duplicate specification', function () {
    $graph = FieldRulesGraph::for(TestModel::class)
        ->addSpecification(new NameFieldSpecification());

    expect(fn () => $graph->addSpecification(new NameFieldSpecification()))
        ->toThrow(DuplicateSpecificationException::class);
});

it('throws exception when adding a specification on a field that is already registered', function () {
    $graph = FieldRulesGraph::for(TestModel::class)
        ->addSpecification(new NameFieldSpecification());

    expect(fn () => $graph->addSpecification(new OtherNameFieldSpecification()))
        ->toThrow(DuplicateSpecificationException::class);
});

it('throws exception when adding a specification on a field that is not part of the model', function () {
    FieldRulesGraph::for(TestModel::class)
        ->addSpecification(
            new class extends FieldSpecification
            {
                public function getFieldName(): string
                {
                    return 'unknown_field';
                }
            }
        );
})->throws(FieldNotFoundException::class);

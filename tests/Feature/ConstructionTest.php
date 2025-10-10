<?php

declare(strict_types=1);

use Androlax\LaravelModelStateGraph\FieldRulesGraph;
use Illuminate\Database\Eloquent\Model;
use Tests\Fixtures\Models\TestModel;

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

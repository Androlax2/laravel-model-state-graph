<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * @template T of Model
 */
interface FieldRuleSet
{
    public function getField(): string;

    /**
     * @return BusinessRule<T>[]
     */
    public function getRules(): array;

    /**
     * @param  T  $model
     */
    public function supports(Model $model): bool;
}

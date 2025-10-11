<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * @template T of Model
 */
interface BusinessRule
{
    /**
     * @param  T  $model
     */
    public function supports(Model $model): bool;

    /**
     * @param  T  $model
     */
    public function validate(Model $model): void;
}

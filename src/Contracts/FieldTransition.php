<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Contracts;

/**
 * Represents a single field change between two entity states.
 */
interface FieldTransition
{
    public function getEntity(): object;

    public function getPreviousState(): EntityState;

    public function getCurrentState(): EntityState;

    public function getField(): string;

    public function getPreviousValue(): mixed;

    public function getCurrentValue(): mixed;

    public function hasChanged(): bool;
}

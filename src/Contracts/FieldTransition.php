<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Contracts;

/**
 * Represents a single field change between two entity states.
 */
interface FieldTransition
{
    /**
     * Returns the entity being transitioned.
     */
    public function getEntity(): object;

    /**
     * Returns the previous state of the entity.
     */
    public function getPreviousState(): EntityState;

    /**
     * Returns the current state of the entity.
     */
    public function getCurrentState(): EntityState;

    /**
     * Returns the field name that changed.
     */
    public function getField(): string;

    /**
     * Returns the previous value of the field.
     */
    public function getPreviousValue(): mixed;

    /**
     * Returns the current value of the field.
     */
    public function getCurrentValue(): mixed;

    /**
     * Indicates whether the field value has changed.
     */
    public function hasChanged(): bool;
}

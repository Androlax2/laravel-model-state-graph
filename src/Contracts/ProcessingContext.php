<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Contracts;

/**
 * Provides access to processing data during state transitions.
 */
interface ProcessingContext
{
    /**
     * Sets a computed value for a field.
     */
    public function setComputedValue(string $field, mixed $value): void;

    /**
     * Returns a computed value for a field.
     */
    public function getComputedValue(string $field): mixed;

    /**
     * Indicates whether a computed value exists for a field.
     */
    public function hasComputedValue(string $field): bool;

    /**
     * Sets metadata for use during processing.
     */
    public function setMetadata(string $key, mixed $value): void;

    /**
     * Returns metadata value by key.
     */
    public function getMetadata(string $key, mixed $default = null): mixed;

    /**
     * Returns the current entity state.
     */
    public function getCurrentState(): EntityState;

    /**
     * Returns the previous entity state.
     */
    public function getPreviousState(): EntityState;

    /**
     * Returns the validation context.
     */
    public function getValidationContext(): ValidationContext;

    /**
     * Returns the field transition for a specific field.
     */
    public function getFieldTransition(string $field): ?FieldTransition;

    /**
     * Indicates whether a specific field has changed.
     */
    public function hasFieldChanged(string $field): bool;
}

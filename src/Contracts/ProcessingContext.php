<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Contracts;

/**
 * Provides access to processing data during state transitions.
 */
interface ProcessingContext
{
    public function setComputedValue(string $field, mixed $value): void;

    public function getComputedValue(string $field): mixed;

    public function hasComputedValue(string $field): bool;

    public function setMetadata(string $key, mixed $value): void;

    public function getMetadata(string $key, mixed $default = null): mixed;

    public function getCurrentState(): EntityState;

    public function getPreviousState(): EntityState;

    public function getValidationContext(): ValidationContext;

    public function getFieldTransition(string $field): ?FieldTransition;

    public function hasFieldChanged(string $field): bool;
}

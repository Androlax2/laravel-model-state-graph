<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Contracts;

/**
 * Provides access to validation data during state transitions.
 */
interface ValidationContext
{
    /**
     * Adds a violation for a specific field.
     */
    public function addViolation(FieldViolation $violation): void;

    /**
     * Creates and adds a violation for a specific field.
     */
    public function addFieldViolation(string $field, string $message, ?string $code = null): void;

    /**
     * Returns all recorded violations.
     */
    public function getViolations(): FieldViolationCollection;

    /**
     * Indicates whether any violations have been recorded.
     */
    public function hasViolations(): bool;

    /**
     * Sets metadata for use during validation.
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
     * Returns the field transition for a specific field.
     */
    public function getFieldTransition(string $field): ?FieldTransition;

    /**
     * Indicates whether a specific field has changed.
     */
    public function hasFieldChanged(string $field): bool;
}

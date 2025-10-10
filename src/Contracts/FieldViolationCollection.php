<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Contracts;

/**
 * Collects and manages validation violations.
 */
interface FieldViolationCollection
{
    /**
     * Adds a violation to the collection.
     */
    public function add(FieldViolation $violation): void;

    /**
     * Returns all violations.
     *
     * @return FieldViolation[]
     */
    public function getAll(): array;

    /**
     * Returns violations for a specific field.
     *
     * @return FieldViolation[]
     */
    public function getForField(string $field): array;

    /**
     * Indicates whether any violations exist.
     */
    public function hasViolations(): bool;

    /**
     * Returns the total number of violations.
     */
    public function count(): int;
}

<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Contracts;

/**
 * Represents a single validation violation.
 */
interface FieldViolation
{
    /**
     * Returns the field that caused the violation.
     */
    public function getField(): string;

    /**
     * Returns the violation message.
     */
    public function getMessage(): string;

    /**
     * Returns the violation code for programmatic handling.
     */
    public function getCode(): ?string;
}

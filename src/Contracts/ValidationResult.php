<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Contracts;

/**
 * Represents the result of validating a state transition.
 */
interface ValidationResult
{
    /**
     * Indicates whether the state transition is valid.
     */
    public function isValid(): bool;

    /**
     * Returns any violations that prevent the state transition.
     *
     * @return array<string, string[]>
     */
    public function getViolations(): array;

    /**
     * Returns the processing context if validation was successful.
     */
    public function getProcessingContext(): ?ProcessingContext;
}

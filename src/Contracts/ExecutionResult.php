<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Contracts;

/**
 * Represents the result of executing a state transition.
 */
interface ExecutionResult
{
    /**
     * Indicates whether the state transition executed successfully.
     */
    public function isSuccessful(): bool;

    /**
     * Returns any violations that prevented successful execution.
     */
    public function getViolations(): FieldViolationCollection;

    /**
     * Returns the processing context containing computed values and metadata.
     */
    public function getContext(): ?ProcessingContext;
}

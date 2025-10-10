<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Contracts;

/**
 * Validates the complete entity state after all field processing.
 */
interface GlobalValidator
{
    /**
     * Validates the complete entity state and reports any violations.
     */
    public function validate(ValidationContext $context): void;

    /**
     * Determines if this validator should validate the current context.
     */
    public function shouldValidate(ValidationContext $context): bool;
}

<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Contracts;

/**
 * Validates field transitions against business rules.
 */
interface FieldValidator
{
    /**
     * Validates a field transition and reports any violations.
     */
    public function validate(FieldTransition $transition, ValidationContext $context): void;

    /**
     * Determines if this validator should validate the given field transition.
     */
    public function supports(FieldTransition $transition): bool;
}

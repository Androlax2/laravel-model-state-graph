<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Contracts;

/**
 * Processes field transitions by applying business logic and data transformations.
 */
interface FieldProcessor
{
    /**
     * Applies business logic to process a field transition.
     */
    public function process(FieldTransition $transition, ProcessingContext $context): void;

    /**
     * Determines if this processor should handle the given field transition.
     */
    public function supports(FieldTransition $transition): bool;
}

<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Contracts;

/**
 * Defines the validation, processing, and dependency rules for a specific field
 * during entity state transitions.
 */
interface FieldSpecification
{
    /**
     * Returns the name of the field this specification applies to.
     */
    public function getField(): string;

    /**
     * Returns the validators that should check this field's changes.
     *
     * @return FieldValidator[]
     */
    public function getValidators(): array;

    /**
     * Returns the processors that should handle this field's changes.
     *
     * @return FieldProcessor[]
     */
    public function getProcessors(): array;

    /**
     * Returns the fields that must be processed before this field.
     *
     * @return string[]
     */
    public function getDependencies(): array;
}

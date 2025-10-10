<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Contracts;

/**
 * Defines the business rules, validation, and processing logic for a specific field
 * during state transitions.
 *
 * Each FieldSpecification encapsulates all the behavior for a single field, including:
 * - What validators should run when this field changes
 * - What processors should transform this field's data
 * - What other fields must be processed before this field
 */
interface FieldSpecification
{
    /**
     * Returns the name of the field this specification applies to.
     *
     * This must match an actual attribute or relationship on the entity.
     * The system will validate that this field exists on the entity.
     *
     * @return string The field name (e.g., 'quantity', 'status', 'delivery_type')
     */
    public function getField(): string;

    /**
     * Returns the validators that should check this field's changes.
     *
     * Validators determine if a field change is allowed based on business rules.
     * They run before any processing and can block the entire transition.
     *
     * Example: A quantity increase might require stock availability validation.
     *
     * @return FieldValidator[] Array of validators in execution order
     */
    public function getValidators(): array;

    /**
     * Returns the processors that should handle this field's changes.
     *
     * Processors execute business logic when a field changes, such as:
     * - Recalculating derived values
     * - Updating related data
     * - Preparing side effects
     *
     * Processors run after successful validation.
     *
     * @return FieldProcessor[] Array of processors in execution order
     */
    public function getProcessors(): array;

    /**
     * Returns the fields that must be processed before this field.
     *
     * Dependencies ensure fields are processed in the correct order.
     * For example, 'total' depends on 'price' and 'quantity' because
     * it needs their computed values for calculation.
     *
     * The system will automatically resolve and honor this execution order.
     *
     * @return string[] Array of field names that this field depends on
     *
     * @example ['price', 'discount'] // Process price and discount before this field
     * @example [] // This field has no dependencies, can process first
     */
    public function getDependencies(): array;
}

<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Contracts;

/**
 * Manages the execution of state transitions for entities.
 */
interface StateTransitionManager
{
    /**
     * Adds a field specification to define validation and processing rules.
     */
    public function withSpecification(FieldSpecification $specification): self;

    /**
     * Adds a global validator to check the complete entity state.
     */
    public function withGlobalValidator(GlobalValidator $validator): self;

    /**
     * Adds a context transformer to apply cross-field business logic.
     */
    public function withContextTransformer(ContextTransformer $transformer): self;

    /**
     * Validates a state transition without executing it.
     */
    public function validate(EntityState $current, EntityState $previous): ValidationResult;

    /**
     * Executes a state transition and returns the result.
     */
    public function execute(EntityState $current, EntityState $previous): ExecutionResult;
}

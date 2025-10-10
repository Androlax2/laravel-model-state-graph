<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Contracts;

/**
 * Transforms the processing context by applying business logic that requires
 * coordination across multiple fields or access to the complete entity state.
 *
 * ContextTransformers execute after all individual field processing is complete
 * and have access to the fully processed context. They are responsible for:
 *
 * - Coordinating logic that depends on multiple field changes
 * - Calculating derived values from multiple computed values
 * - Applying business rules that consider the complete entity state
 * - Preparing side effects or external system updates
 * - Managing cross-field dependencies and consistency
 *
 * Transformers are conditional - they only execute when their shouldTransform()
 * method returns true, allowing for efficient processing of complex scenarios.
 */
interface ContextTransformer
{
    /**
     * Applies business logic to transform the processing context.
     *
     * This method has access to all field changes, computed values, and metadata
     * from previous processing steps. It can modify the context by setting new
     * computed values, updating metadata, or coordinating complex operations.
     *
     * @param  ProcessingContext  $context  The current processing context
     */
    public function transform(ProcessingContext $context): void;

    /**
     * Determines if this transformer should execute for the current context.
     *
     * @param  ProcessingContext  $context  The processing context to evaluate
     * @return bool True if the transformer should run, false to skip
     */
    public function shouldTransform(ProcessingContext $context): bool;
}

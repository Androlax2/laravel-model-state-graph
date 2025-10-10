<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph;

use Androlax\LaravelModelStateGraph\Contracts\ContextTransformer;
use Androlax\LaravelModelStateGraph\Contracts\ExecutionResult;
use Androlax\LaravelModelStateGraph\Contracts\FieldSpecification;
use Androlax\LaravelModelStateGraph\Contracts\GlobalValidator;
use Androlax\LaravelModelStateGraph\Contracts\StateTransitionManager;
use Androlax\LaravelModelStateGraph\Exceptions\FieldNotFoundException;
use Androlax\LaravelModelStateGraph\Violation\FieldViolationCollection;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

final readonly class ModelStateGraph implements StateTransitionManager
{
    private SpecificationCollection $specifications;

    /**
     * @param  class-string<Model>  $modelClassName
     */
    public function __construct(
        private string $modelClassName
    ) {
        $this->specifications = new SpecificationCollection();
    }

    public function withSpecification(FieldSpecification $specification): self
    {
        $this->specifications->add($specification);

        return $this;
    }

    public function withGlobalValidator(GlobalValidator $validator): StateTransitionManager
    {
        throw new RuntimeException('Not implemented yet.');
    }

    public function withContextTransformer(ContextTransformer $transformer): StateTransitionManager
    {
        throw new RuntimeException('Not implemented yet.');
    }

    /**
     * @throws FieldNotFoundException
     */
    public function validate(Model $current, Model $previous): ValidationResult
    {
        $this->specifications->validateFieldsExist($current);

        $violations = new FieldViolationCollection();
        $validationContext = new BasicValidationContext($current, $previous, $violations);

        foreach ($this->specifications->getAll() as $field => $specification) {
            $currentValue = $current->getAttribute($field);
            $previousValue = $previous->getAttribute($field);

            if ($currentValue === $previousValue) {
                continue;
            }

            $transition = new BasicFieldTransition($current, $previous, $field);

            foreach ($specification->getValidators() as $validator) {
                if ($validator->supports($transition)) {
                    $validator->validate($transition, $validationContext);
                }
            }
        }

        return new ValidationResult($violations);
    }

    public function execute(Model $current, Model $previous): ExecutionResult
    {
        throw new RuntimeException('Not implemented yet.');
    }
}

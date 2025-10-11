<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph;

use Androlax\LaravelModelStateGraph\Contexts\TransitionContext;
use Androlax\LaravelModelStateGraph\Exceptions\FieldModificationException;
use Androlax\LaravelModelStateGraph\Specification\FieldSpecification;
use Androlax\LaravelModelStateGraph\Specification\FieldSpecificationRegistry;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final readonly class FieldRulesGraph
{
    private FieldSpecificationRegistry $registry;

    /**
     * @param  class-string<Model>  $entityClass
     */
    public function __construct(private string $entityClass)
    {
        $this->validateEntityClass($entityClass);

        $this->registry = new FieldSpecificationRegistry($entityClass);
    }

    /**
     * @param  class-string<Model>  $entityClass
     */
    public static function for(string $entityClass): self
    {
        return new self($entityClass);
    }

    /**
     * @return class-string<Model>
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function addSpecification(FieldSpecification $specification): self
    {
        $this->registry->register($specification);

        return $this;
    }

    /**
     * @param  FieldSpecification[]  $specifications
     */
    public function addSpecifications(array $specifications): self
    {
        foreach ($specifications as $specification) {
            $this->addSpecification($specification);
        }

        return $this;
    }

    public function getRegistry(): FieldSpecificationRegistry
    {
        return $this->registry;
    }

    public function executeStateTransition(
        Model $model,
    ): void {
        $changedFields = $this->getChangedFields($model);

        foreach ($changedFields as $field) {
            $this->validateFieldTransition($model, $field);
        }
    }

    private function validateFieldTransition(
        Model $model,
        string $field,
    ): void {
        $specification = $this->registry->getSpecification($field);

        if (! $specification instanceof FieldSpecification) {
            return;
        }

        $context = new TransitionContext($model, $field);

        throw_unless(
            $specification->canModify($context),
            new FieldModificationException(
                "Field '{$field}' cannot be modified.",
            )
        );
    }

    /**
     * @return string[]
     */
    private function getChangedFields(Model $model): array
    {
        return array_filter(
            array_keys($model->getDirty()),
            fn (string $field): bool => ! in_array(
                $field,
                ['created_at', 'updated_at', 'deleted_at'],
                true
            )
        );
    }

    /**
     * @param  class-string  $entityClass
     */
    private function validateEntityClass(string $entityClass): void
    {
        throw_unless(
            is_subclass_of($entityClass, Model::class),
            new InvalidArgumentException(
                sprintf('Class %s must be an instance of %s', $entityClass, Model::class)
            )
        );
    }
}

<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph;

use Androlax\LaravelModelStateGraph\Exceptions\FieldNotFoundException;
use Androlax\LaravelModelStateGraph\Specification\FieldSpecification;
use Androlax\LaravelModelStateGraph\Specification\FieldSpecificationRegistry;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class FieldRulesGraph
{
    private FieldSpecificationRegistry $registry;

    /**
     * @param  class-string<Model>  $entityClass
     */
    public function __construct(private readonly string $entityClass)
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

    /**
     * @throws FieldNotFoundException
     */
    public function addSpecification(FieldSpecification $specification): self
    {
        $this->registry->register($specification);

        return $this;
    }

    /**
     * @param  FieldSpecification[]  $specifications
     *
     * @throws FieldNotFoundException
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

    /**
     * @param  class-string  $entityClass
     */
    private function validateEntityClass(string $entityClass): void
    {
        if (! is_subclass_of($entityClass, Model::class)) {
            throw new InvalidArgumentException(
                sprintf("Class {$entityClass} must be an instance of %s", Model::class)
            );
        }
    }
}

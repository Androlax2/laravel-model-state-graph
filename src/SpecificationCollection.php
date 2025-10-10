<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph;

use Androlax\LaravelModelStateGraph\Contracts\FieldSpecification;
use Androlax\LaravelModelStateGraph\Exceptions\FieldNotFoundException;

final class SpecificationCollection
{
    /**
     * @var array<string, FieldSpecification>
     */
    private array $specifications = [];

    public function add(FieldSpecification $specification): void
    {
        $this->specifications[$specification->getField()] = $specification;
    }

    public function get(string $field): ?FieldSpecification
    {
        return $this->specifications[$field] ?? null;
    }

    public function getAll(): array
    {
        return $this->specifications;
    }

    public function has(string $field): bool
    {
        return isset($this->specifications[$field]);
    }

    /**
     * @throws FieldNotFoundException
     */
    public function validateFieldsExist(object $model): void
    {
        $modelAttributes = array_keys($model->getAttributes());

        foreach (array_keys($this->specifications) as $field) {
            if (! in_array($field, $modelAttributes)) {
                throw FieldNotFoundException::forField($field, $model);
            }
        }
    }

    public function getDependencyGraph(): array
    {
        return array_map(fn (FieldSpecification $specification): array => $specification->getDependencies(), $this->specifications);
    }
}

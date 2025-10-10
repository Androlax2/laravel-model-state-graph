<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Specification;

use Androlax\LaravelModelStateGraph\Exceptions\DuplicateSpecificationException;
use Androlax\LaravelModelStateGraph\Exceptions\FieldNotFoundException;
use Illuminate\Database\Eloquent\Model;

final class FieldSpecificationRegistry
{
    /**
     * @var array<string, FieldSpecification>
     */
    private array $specifications = [];

    /**
     * @param  class-string<Model>  $entityClass
     */
    public function __construct(private readonly string $entityClass) {}

    /**
     * @throws FieldNotFoundException
     */
    public function register(FieldSpecification $specification): void
    {
        $fieldName = $specification->getFieldName();

        if ($this->hasSpecification($fieldName)) {
            throw new DuplicateSpecificationException(
                "Field '{$fieldName}' already has a specification"
            );
        }

        if (! $this->fieldExistsInEntity($fieldName)) {
            throw FieldNotFoundException::forField($fieldName, new $this->entityClass());
        }

        $this->specifications[$fieldName] = $specification;
    }

    public function getSpecification(string $fieldName): ?FieldSpecification
    {
        return $this->specifications[$fieldName] ?? null;
    }

    public function hasSpecification(string $fieldName): bool
    {
        return array_key_exists($fieldName, $this->specifications);
    }

    private function fieldExistsInEntity(string $fieldName): bool
    {
        $model = new $this->entityClass();
        $table = $model->getTable();
        $columns = $model->getConnection()->getSchemaBuilder()->getColumnListing($table);

        return in_array($fieldName, $columns, true);
    }
}

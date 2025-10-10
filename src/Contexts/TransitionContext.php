<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Contexts;

use Illuminate\Database\Eloquent\Model;

final readonly class TransitionContext
{
    public function __construct(
        private Model $model,
        private string $fieldName,
    ) {}

    public function model(): Model
    {
        return $this->model;
    }

    public function field(): string
    {
        return $this->fieldName;
    }

    public function oldValue(): mixed
    {
        return $this->model->getOriginal($this->fieldName);
    }

    public function newValue(): mixed
    {
        return $this->getAttributeValue($this->fieldName);
    }

    public function original(?string $field = null): mixed
    {
        if ($field !== null && $field !== '' && $field !== '0') {
            return $this->model->getOriginal($field);
        }

        return $this->model->getOriginal();
    }

    public function current(?string $field = null): mixed
    {
        if ($field !== null && $field !== '' && $field !== '0') {
            return $this->getAttributeValue($field);
        }

        return $this->model->getAttributes();
    }

    public function isDirty(): bool
    {
        return $this->model->isDirty($this->fieldName);
    }

    public function isClean(): bool
    {
        return $this->model->isClean($this->fieldName);
    }

    public function wasChanged(): bool
    {
        return $this->model->wasChanged($this->fieldName);
    }

    /**
     * @param  array<string>  $fields
     */
    public function isAnyDirty(array $fields = []): bool
    {
        if ($fields === []) {
            return $this->model->isDirty();
        }

        if (count($fields) === 1) {
            return $this->model->isDirty($fields[0]);
        }

        return $this->model->isDirty($fields);
    }

    /**
     * @param  array<string>  $fields
     */
    public function isAllClean(array $fields = []): bool
    {
        if ($fields === []) {
            return $this->model->isClean();
        }

        if (count($fields) === 1) {
            return $this->model->isClean($fields[0]);
        }

        return $this->model->isClean($fields);
    }

    /**
     * @return array<string>
     */
    public function changedFields(): array
    {
        return array_keys($this->model->getDirty());
    }

    /**
     * @return array<string, mixed>
     */
    public function changes(): array
    {
        return $this->model->getChanges();
    }

    public function hasChanged(string $field): bool
    {
        return $this->model->isDirty($field);
    }

    public function valueIncreased(): bool
    {
        $newValue = $this->getAttributeValue($this->fieldName);
        $oldValue = $this->oldValue();

        if (! is_numeric($newValue) || ! is_numeric($oldValue)) {
            return false;
        }

        return $newValue > $oldValue;
    }

    public function valueDecreased(): bool
    {
        $newValue = $this->getAttributeValue($this->fieldName);
        $oldValue = $this->oldValue();

        if (! is_numeric($newValue) || ! is_numeric($oldValue)) {
            return false;
        }

        return $newValue < $oldValue;
    }

    public function valueChangedTo(mixed $value): bool
    {
        $currentValue = $this->getAttributeValue($this->fieldName);

        return $currentValue === $value;
    }

    public function valueChangedFrom(mixed $value): bool
    {
        return $this->oldValue() === $value;
    }

    private function getAttributeValue(string $field): mixed
    {
        return $this->model->getAttribute($field);
    }
}

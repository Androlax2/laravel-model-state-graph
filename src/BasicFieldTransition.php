<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph;

use Androlax\LaravelModelStateGraph\Contracts\EntityState;
use Androlax\LaravelModelStateGraph\Contracts\FieldTransition;

final class BasicFieldTransition implements FieldTransition
{
    public function __construct(
        private readonly object $current,
        private readonly object $previous,
        private string $field
    ) {}

    public function getEntity(): object
    {
        return $this->current;
    }

    public function getPreviousState(): EntityState
    {
        return new BasicEntityState($this->previous);
    }

    public function getCurrentState(): EntityState
    {
        return new BasicEntityState($this->current);
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getPreviousValue(): mixed
    {
        return $this->previous->getAttribute($this->field);
    }

    public function getCurrentValue(): mixed
    {
        return $this->current->getAttribute($this->field);
    }

    public function hasChanged(): bool
    {
        return $this->getCurrentValue() !== $this->getPreviousValue();
    }
}

<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph;

use Androlax\LaravelModelStateGraph\Contracts\EntityState;

final readonly class BasicEntityState implements EntityState
{
    public function __construct(
        private object $entity
    ) {}

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getAttributes(): array
    {
        return $this->entity->getAttributes();
    }

    public function getAttribute(string $key): mixed
    {
        return $this->entity->getAttribute($key);
    }
}

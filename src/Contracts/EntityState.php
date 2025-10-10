<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Contracts;

/**
 * Represents the state of an entity at a specific point in time.
 */
interface EntityState
{
    /**
     * Returns the underlying entity instance.
     */
    public function getEntity(): object;

    /**
     * Returns all attributes of the entity.
     *
     * @return array<string, mixed>
     */
    public function getAttributes(): array;

    /**
     * Returns the value of a specific attribute.
     */
    public function getAttribute(string $key): mixed;
}

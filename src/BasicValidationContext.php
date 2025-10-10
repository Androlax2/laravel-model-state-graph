<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph;

use Androlax\LaravelModelStateGraph\Contracts\EntityState;
use Androlax\LaravelModelStateGraph\Contracts\FieldTransition;
use Androlax\LaravelModelStateGraph\Contracts\FieldViolation as FieldViolationContract;
use Androlax\LaravelModelStateGraph\Contracts\FieldViolationCollection;
use Androlax\LaravelModelStateGraph\Contracts\ValidationContext;
use Androlax\LaravelModelStateGraph\Violation\FieldViolation;

final readonly class BasicValidationContext implements ValidationContext
{
    public function __construct(
        private object $current,
        private object $previous,
        private FieldViolationCollection $violations
    ) {}

    public function addViolation(FieldViolationContract $violation): void
    {
        $this->violations->add($violation);
    }

    public function addFieldViolation(string $field, string $message, ?string $code = null): void
    {
        $this->addViolation(new FieldViolation($field, $message, $code));
    }

    public function getViolations(): FieldViolationCollection
    {
        return $this->violations;
    }

    public function hasViolations(): bool
    {
        return $this->violations->hasViolations();
    }

    public function setMetadata(string $key, mixed $value): void
    {
        // TODO: Implement
    }

    public function getMetadata(string $key, mixed $default = null): mixed
    {
        // TODO: Implement
        return $default;
    }

    public function getCurrentState(): EntityState
    {
        return new BasicEntityState($this->current);
    }

    public function getPreviousState(): EntityState
    {
        return new BasicEntityState($this->previous);
    }

    public function getFieldTransition(string $field): ?FieldTransition
    {
        $currentValue = $this->current->getAttribute($field);
        $previousValue = $this->previous->getAttribute($field);

        if ($currentValue === $previousValue) {
            return null;
        }

        return new BasicFieldTransition($this->current, $this->previous, $field);
    }

    public function hasFieldChanged(string $field): bool
    {
        return $this->current->getAttribute($field) !== $this->previous->getAttribute($field);
    }
}

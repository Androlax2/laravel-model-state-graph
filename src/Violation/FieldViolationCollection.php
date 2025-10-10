<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Violation;

use Androlax\LaravelModelStateGraph\Contracts\FieldViolation;
use Androlax\LaravelModelStateGraph\Contracts\FieldViolationCollection as ViolationCollectionContract;

final class FieldViolationCollection implements ViolationCollectionContract
{
    /**
     * @var FieldViolation[]
     */
    private array $violations = [];

    public function add(FieldViolation $violation): void
    {
        $this->violations[] = $violation;
    }

    public function getAll(): array
    {
        return $this->violations;
    }

    public function getForField(string $field): array
    {
        return array_filter(
            $this->violations,
            fn (FieldViolation $v): bool => $v->getField() === $field
        );
    }

    public function hasViolations(): bool
    {
        return $this->violations !== [];
    }

    public function count(): int
    {
        return count($this->violations);
    }
}

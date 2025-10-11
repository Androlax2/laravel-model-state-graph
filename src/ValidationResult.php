<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph;

final readonly class ValidationResult
{
    public function __construct(
        private bool $isValid,
        private array $violations = []
    ) {}

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function getViolations(): array
    {
        return $this->violations;
    }
}

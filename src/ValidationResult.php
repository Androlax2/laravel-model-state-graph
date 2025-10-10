<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph;

use Androlax\LaravelModelStateGraph\Contracts\FieldViolationCollection;
use Androlax\LaravelModelStateGraph\Contracts\ProcessingContext;
use Androlax\LaravelModelStateGraph\Contracts\ValidationResult as ValidationResultContract;

final readonly class ValidationResult implements ValidationResultContract
{
    public function __construct(
        private FieldViolationCollection $violations,
        private ?ProcessingContext $context = null
    ) {}

    public function isValid(): bool
    {
        return ! $this->violations->hasViolations();
    }

    public function getViolations(): FieldViolationCollection
    {
        return $this->violations;
    }

    public function getProcessingContext(): ?ProcessingContext
    {
        return $this->context;
    }
}

<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Violation;

use Androlax\LaravelModelStateGraph\Contracts\FieldViolation as ViolationContract;

final readonly class FieldViolation implements ViolationContract
{
    public function __construct(
        private string $field,
        private string $message,
        private ?string $code = null
    ) {}

    public function getField(): string
    {
        return $this->field;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }
}

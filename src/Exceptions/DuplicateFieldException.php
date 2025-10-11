<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Exceptions;

use DomainException;

final class DuplicateFieldException extends DomainException
{
    public function __construct(string $field)
    {
        parent::__construct("Field rule set for '{$field}' is already registered");
    }
}

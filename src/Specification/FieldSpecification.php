<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Specification;

abstract class FieldSpecification
{
    abstract public function getFieldName(): string;
}

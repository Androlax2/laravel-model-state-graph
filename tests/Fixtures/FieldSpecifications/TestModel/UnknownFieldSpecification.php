<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Tests\Fixtures\FieldSpecifications\TestModel;

use Androlax\LaravelModelStateGraph\Contracts\FieldSpecification;

final class UnknownFieldSpecification implements FieldSpecification
{
    public function getFieldName(): string
    {
        return 'unknown_field';
    }
}

<?php

declare(strict_types=1);

namespace Tests\Fixtures\ModelStateGraph\TestModel\FieldSpecifications;

use Androlax\LaravelModelStateGraph\Contracts\FieldSpecification;

final class UnknownFieldSpecification implements FieldSpecification
{
    public function getField(): string
    {
        return 'unknown_field';
    }

    public function getValidators(): array
    {
        return [];
    }

    public function getProcessors(): array
    {
        return [];
    }

    public function getDependencies(): array
    {
        return [];
    }
}

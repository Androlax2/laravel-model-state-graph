<?php

declare(strict_types=1);

namespace Tests\Fixtures\ModelStateGraph\TestModel\FieldSpecifications;

use Androlax\LaravelModelStateGraph\Contracts\FieldSpecification;
use Tests\Fixtures\ModelStateGraph\TestModel\Validators\RequiredValidator;

final class NameFieldSpecificationWithRequiredValidator implements FieldSpecification
{
    public function getField(): string
    {
        return 'name';
    }

    public function getValidators(): array
    {
        return [
            new RequiredValidator(),
        ];
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

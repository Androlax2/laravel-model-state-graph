<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Contracts;

interface FieldSpecification
{
    public function getFieldName(): string;

    /*public function getValidators(): array;

    public function getProcessors(): array;

    public function getDependencies(): array;*/
}

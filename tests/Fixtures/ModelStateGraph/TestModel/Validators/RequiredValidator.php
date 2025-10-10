<?php

declare(strict_types=1);

namespace Tests\Fixtures\ModelStateGraph\TestModel\Validators;

use Androlax\LaravelModelStateGraph\Contracts\FieldTransition;
use Androlax\LaravelModelStateGraph\Contracts\FieldValidator;
use Androlax\LaravelModelStateGraph\Contracts\ValidationContext;

final class RequiredValidator implements FieldValidator
{
    public function validate(FieldTransition $transition, ValidationContext $context): void
    {
        if (empty($transition->getCurrentValue())) {
            $context->addFieldViolation($transition->getField(), 'Field is required');
        }
    }

    public function supports(FieldTransition $transition): bool
    {
        return $transition->getField() === 'name';
    }
}

<?php

declare(strict_types=1);

namespace Tests\Fixtures\Specifications;

use Androlax\LaravelModelStateGraph\Contexts\TransitionContext;
use Androlax\LaravelModelStateGraph\Specification\FieldSpecification;

final class QuantityFieldSpecification extends FieldSpecification
{
    public function getFieldName(): string
    {
        return 'quantity';
    }

    public function canModify(TransitionContext $context): bool
    {
        return true;
    }
}

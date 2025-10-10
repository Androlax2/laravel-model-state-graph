<?php

declare(strict_types=1);

namespace Tests\Fixtures\Specifications;

use Androlax\LaravelModelStateGraph\Contexts\TransitionContext;
use Androlax\LaravelModelStateGraph\Specification\FieldSpecification;

final class NameFieldSpecification extends FieldSpecification
{
    public function getFieldName(): string
    {
        return 'name';
    }

    public function canModify(TransitionContext $context): bool
    {
        return true;
    }
}

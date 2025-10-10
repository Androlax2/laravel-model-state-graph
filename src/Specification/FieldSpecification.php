<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Specification;

use Androlax\LaravelModelStateGraph\Contexts\TransitionContext;

abstract class FieldSpecification
{
    abstract public function getFieldName(): string;

    abstract public function canModify(TransitionContext $context): bool;
}

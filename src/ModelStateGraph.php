<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph;

use Androlax\LaravelModelStateGraph\Contracts\FieldSpecification;
use Androlax\LaravelModelStateGraph\Exceptions\FieldNotFoundException;
use Illuminate\Database\Eloquent\Model;

final class ModelStateGraph
{
    /**
     * @var list<FieldSpecification>
     */
    private array $specifications = [];

    public function __construct(
        private Model $subject
    ) {}

    /**
     * @throws FieldNotFoundException
     */
    public function withSpecification(FieldSpecification $specification): self
    {
        $field = $specification->getFieldName();
        if (! $this->subject->hasAttribute($field)) {
            throw FieldNotFoundException::forField($field, $this->subject);
        }

        $this->specifications[] = $specification;

        return $this;
    }
}

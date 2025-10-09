<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Tests\Fixtures\FieldSpecifications\TestModel;

use Androlax\LaravelModelStateGraph\Contracts\FieldSpecification;

final class NameFieldSpecification implements FieldSpecification
{
    public function getFieldName(): string
    {
        return 'name';
    }

    /*public function getValidators(): array
    {
        return [
            new StockAvailabilityValidator(),
            new MaxIncreaseValidator(50),
            new CustomerTierQuantityValidator(),
        ];
    }

    public function getProcessors(): array
    {
        return [
            new TotalRecalculationProcessor(),
            new StockReservationProcessor(),
            new DiscountApplicationProcessor(),
        ];
    }

     public function getDependencies(): array
    {
        return [
            "currency",
            "tax_rate"
        ]; // Validate currency/tax_rate first
    }

    */
}

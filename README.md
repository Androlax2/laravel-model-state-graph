# Laravel Model State Graph

[![Latest Version on Packagist](https://img.shields.io/packagist/v/androlax2/laravel-model-state-graph.svg?style=flat-square)](https://packagist.org/packages/androlax2/laravel-model-state-graph)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/androlax2/laravel-model-state-graph/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/Androlax2/laravel-model-state-graph/actions?query=workflow%3Atests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/androlax2/laravel-model-state-graph/formats.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/Androlax2/laravel-model-state-graph/actions?query=workflow%3AFormats+branch%3Amain++)
[![Total Downloads](https://img.shields.io/packagist/dt/androlax2/laravel-model-state-graph.svg?style=flat-square)](https://packagist.org/packages/androlax2/laravel-model-state-graph)

## About

Laravel Model State Graph provides a powerful, flexible way to enforce complex business rules and state transitions in your Eloquent models.
Instead of scattering validation logic across controllers, form requests, and model observers, this package lets you define field-level
business rules that are conditionally applied based on your model's current state.

Think of it as a state machine for individual model fields, where you can control what changes are allowed, when they're allowed, and under
what conditions.

## When to Use

This package is ideal for scenarios where you need to:

- **Enforce state transitions**: Control valid status workflows (e.g., draft → pending → approved → shipped)
- **Manage inventory constraints**: Ensure quantity changes respect stock levels, daily limits, and contractual obligations
- **Implement approval workflows**: Require different validation rules based on user roles or approval states
- **Guard critical fields**: Prevent invalid updates to prices, quantities, or statuses that could break business logic
- **Maintain data integrity**: Ensure models maintain valid states throughout their lifecycle

### Real-World Examples

- E-commerce order management with complex status transitions
- Inventory systems with minimum/maximum stock rules
- Document approval workflows with role-based validations
- Pricing systems requiring approval for discounts above certain thresholds
- Booking systems where cancellations have state-dependent rules

## Requirements

- PHP 8.2 or higher
- Laravel 10.0 or higher

## Installation

Install the package via composer:

```bash
composer require androlax2/laravel-model-state-graph
```

## Core Concepts

The package is built around three main interfaces:

### BusinessRule Interface

Individual validation rules that can be conditionally applied:

```php
interface BusinessRule
{
    /**
     * Determine if this rule applies to the current model state
     */
    public function supports(Model $model): bool;
    
    /**
     * Validate the model against this rule
     * @throws BusinessRuleViolationException
     */
    public function validate(Model $model): void;
}
```

### FieldRuleSet Interface

Groups related business rules for a specific model field:

```php
interface FieldRuleSet
{
    /**
     * The field name this rule set validates
     */
    public function getField(): string;
    
    /**
     * All business rules for this field
     * @return BusinessRule[]
     */
    public function getRules(): array;
    
    /**
     * Determine if this rule set should run for the current model state
     */
    public function supports(Model $model): bool;
}
```

### ModelStateGraph

Coordinates rule execution:

```php
$graph = ModelStateGraph::for(Product::class)
    ->addFieldRuleSet(new QuantityRuleSet())
    ->addFieldRuleSet(new PriceRuleSet());

if ($graph->isValid($model)) {
    // All rules passed
} else {
    $violations = $graph->getViolations($model);
    // Handle violations
}
```

## Quick Start

Let's start with the simplest possible example - validating a single field:

### Step 1: Create a Business Rule

```php
<?php

namespace App\Rules;

use Androlax\LaravelModelStateGraph\Contracts\BusinessRule;
use Androlax\LaravelModelStateGraph\Exceptions\BusinessRuleViolationException;
use App\Models\Product;

class QuantityMustBePositiveRule implements BusinessRule
{
    public function supports(Product $model): bool
    {
        // Only apply when quantity changes
        return $model->isDirty('quantity');
    }
    
    public function validate(Product $model): void
    {
        if ($model->quantity < 0) {
            throw new BusinessRuleViolationException('Quantity must be positive');
        }
    }
}
```

### Step 2: Create a Field Rule Set

```php
<?php

namespace App\RuleSets;

use Androlax\LaravelModelStateGraph\Contracts\FieldRuleSet;
use App\Models\Product;
use App\Rules\QuantityMustBePositiveRule;

class QuantityRuleSet implements FieldRuleSet
{
    public function getField(): string
    {
        return 'quantity';
    }
    
    public function getRules(): array
    {
        return [
            new QuantityMustBePositiveRule(),
        ];
    }
    
    public function supports(Product $model): bool
    {
        return $model->isDirty('quantity');
    }
}
```

### Step 3: Validate Your Model

```php
<?php

use Androlax\LaravelModelStateGraph\ModelStateGraph;
use App\RuleSets\QuantityRuleSet;

$product = Product::first();
$product->quantity = -5;

$graph = ModelStateGraph::for(Product::class)
    ->addFieldRuleSet(new QuantityRuleSet());

if ($graph->isValid($product)) {
    $product->save();
} else {
    $violations = $graph->getViolations($product);
    // Output: ["Quantity must be positive"]
}
```

## Basic Usage

### Multiple Rules for a Single Field

```php
<?php

class QuantityRuleSet implements FieldRuleSet
{
    public function getField(): string
    {
        return 'quantity';
    }
    
    public function getRules(): array
    {
        return [
            new QuantityMustBePositiveRule(),
            new QuantityIncreaseRule(),
            new QuantityDecreaseRule(),
            new QuantityRangeRule(),
        ];
    }
    
    public function supports(Product $model): bool
    {
        return $model->isDirty('quantity');
    }
}
```

### Conditional Business Rules

Rules that only apply in specific scenarios:

```php
<?php

class QuantityIncreaseRule implements BusinessRule
{
    public function supports(Product $model): bool
    {
        // Only when increasing quantity
        return $model->isDirty('quantity') && 
               $model->quantity > $model->getOriginal('quantity');
    }
    
    public function validate(Product $model): void
    {
        $increase = $model->quantity - $model->getOriginal('quantity');
        
        if ($increase > $model->max_daily_increase) {
            throw new BusinessRuleViolationException(
                "Quantity increase of {$increase} exceeds daily limit of {$model->max_daily_increase}"
            );
        }
        
        if (!$this->hasSufficientInventory($increase)) {
            throw new BusinessRuleViolationException('Insufficient inventory for quantity increase');
        }
    }
    
    private function hasSufficientInventory(int $increase): bool
    {
        // Your inventory check logic
        return true;
    }
}

class QuantityDecreaseRule implements BusinessRule
{
    public function supports(Product $model): bool
    {
        // Only when decreasing quantity
        return $model->isDirty('quantity') && 
               $model->quantity < $model->getOriginal('quantity');
    }
    
    public function validate(Product $model): void
    {
        if ($model->quantity < $model->minimum_stock) {
            throw new BusinessRuleViolationException(
                "Quantity cannot decrease below minimum stock level of {$model->minimum_stock}"
            );
        }
    }
}
```

### Validating Multiple Fields

```php
<?php

$graph = ModelStateGraph::for(Product::class)
    ->addFieldRuleSet(new QuantityRuleSet())
    ->addFieldRuleSet(new PriceRuleSet())
    ->addFieldRuleSet(new StatusRuleSet());

$product->fill([
    'quantity' => 25,
    'price' => 99.99,
    'status' => 'active'
]);

if ($graph->isValid($product)) {
    $product->save();
} else {
    $violations = $graph->getViolations($product);
    foreach ($violations as $violation) {
        echo $violation . "\n";
    }
}
```

## Advanced Usage

### Status Transition Rules

Control complex state machine workflows:

```php
<?php

class StatusTransitionRule implements BusinessRule
{
    private array $allowedTransitions = [
        'draft' => ['pending', 'cancelled'],
        'pending' => ['approved', 'rejected', 'cancelled'],
        'approved' => ['shipped', 'cancelled'],
        'shipped' => ['delivered'],
        'delivered' => [], // Terminal state
    ];
    
    public function supports(Product $model): bool
    {
        return $model->isDirty('status');
    }
    
    public function validate(Product $model): void
    {
        $from = $model->getOriginal('status');
        $to = $model->status;
        
        $allowed = $this->allowedTransitions[$from] ?? [];
        
        if (!in_array($to, $allowed)) {
            throw new BusinessRuleViolationException(
                "Cannot transition from '{$from}' to '{$to}'. Allowed transitions: " . implode(', ', $allowed)
            );
        }
    }
}
```

### Conditional Rule Sets with Dependencies

Inject services and apply rules based on feature flags, user roles, or other context:

```php
<?php

class ConditionalPriceRuleSet implements FieldRuleSet
{
    public function __construct(
        private FeatureFlagService $features,
        private User $currentUser
    ) {}
    
    public function getField(): string
    {
        return 'price';
    }
    
    public function getRules(): array
    {
        $rules = [
            new PriceRangeRule(),
            new PriceChangeLimitRule(),
        ];
        
        // Admin users can override price limits
        if ($this->currentUser->hasRole('admin')) {
            $rules[] = new AdminPriceOverrideRule();
        }
        
        // Add experimental pricing rules when feature is enabled
        if ($this->features->isEnabled('dynamic_pricing')) {
            $rules[] = new DynamicPricingRule();
        }
        
        return $rules;
    }
    
    public function supports(Product $model): bool
    {
        // Skip validation for free products
        return $model->isDirty('price') && $model->category !== 'free';
    }
}

// Usage with dependency injection
$graph = ModelStateGraph::for(Product::class)
    ->addFieldRuleSet(
        new ConditionalPriceRuleSet(
            app(FeatureFlagService::class),
            auth()->user()
        )
    );
```

### Complex Business Rules with External Dependencies

```php
<?php

class PriceRequiresApprovalRule implements BusinessRule
{
    private const APPROVAL_THRESHOLD_PERCENT = 20;
    
    public function __construct(
        private ApprovalService $approvalService
    ) {}
    
    public function supports(Product $model): bool
    {
        if (!$model->isDirty('price')) {
            return false;
        }
        
        $originalPrice = $model->getOriginal('price');
        $newPrice = $model->price;
        $percentChange = abs(($newPrice - $originalPrice) / $originalPrice * 100);
        
        return $percentChange >= self::APPROVAL_THRESHOLD_PERCENT;
    }
    
    public function validate(Product $model): void
    {
        $approval = $this->approvalService->findPendingApproval($model, 'price_change');
        
        if (!$approval || !$approval->isApproved()) {
            throw new BusinessRuleViolationException(
                'Price changes over 20% require manager approval'
            );
        }
    }
}
```

### Integration with Laravel Events

You can integrate the graph into your model lifecycle using Laravel events:

```php
<?php

namespace App\Observers;

use Androlax\LaravelModelStateGraph\ModelStateGraph;
use App\Models\Product;
use App\RuleSets\QuantityRuleSet;
use App\RuleSets\PriceRuleSet;
use App\RuleSets\StatusRuleSet;

class ProductObserver
{
    private ModelStateGraph $graph;
    
    public function __construct()
    {
        $this->graph = ModelStateGraph::for(Product::class)
            ->addFieldRuleSet(new QuantityRuleSet())
            ->addFieldRuleSet(new PriceRuleSet())
            ->addFieldRuleSet(new StatusRuleSet());
    }
    
    public function saving(Product $product): bool
    {
        if (!$this->graph->isValid($product)) {
            $violations = $this->graph->getViolations($product);
            
            // Log violations
            logger()->warning('Product validation failed', [
                'product_id' => $product->id,
                'violations' => $violations,
            ]);
            
            // Prevent save
            return false;
        }
        
        return true;
    }
}
```

## Error Handling

The package provides specific exceptions for different error scenarios:

```php
<?php

use Androlax\LaravelModelStateGraph\ModelStateGraph;
use Androlax\LaravelModelStateGraph\Exceptions\InvalidFieldException;
use Androlax\LaravelModelStateGraph\Exceptions\DuplicateFieldException;
use Androlax\LaravelModelStateGraph\Exceptions\BusinessRuleViolationException;

try {
    $graph = ModelStateGraph::for(Product::class)
        ->addFieldRuleSet(new QuantityRuleSet())
        ->addFieldRuleSet(new PriceRuleSet());
    
    if (!$graph->isValid($product)) {
        $violations = $graph->getViolations($product);
        
        // Log each violation
        foreach ($violations as $violation) {
            logger()->warning('Business rule violation', [
                'model' => get_class($product),
                'model_id' => $product->id,
                'message' => $violation,
            ]);
        }
        
        // Return to user with errors
        return back()->withErrors([
            'validation' => 'The product state is invalid: ' . implode(', ', $violations)
        ]);
    }
    
    $product->save();
    
} catch (InvalidFieldException $e) {
    // Field doesn't exist on the model
    report($e);
    return back()->withErrors([
        'field' => 'Invalid field configuration: ' . $e->getMessage()
    ]);
    
} catch (DuplicateFieldException $e) {
    // Multiple rule sets defined for the same field
    report($e);
    return back()->withErrors([
        'configuration' => 'Duplicate field rule sets: ' . $e->getMessage()
    ]);
}
```

## Testing

### Testing Business Rules

```php
<?php

use Tests\Fixtures\Product;
use App\Rules\QuantityIncreaseRule;

it('allows quantity increases within limits', function () {
    $product = Product::create([
        'quantity' => 10,
        'max_daily_increase' => 50,
    ]);
    
    $product->quantity = 25; // Increase of 15
    
    $rule = new QuantityIncreaseRule();
    
    expect($rule->supports($product))->toBeTrue();
    
    // Should not throw exception
    $rule->validate($product);
});

it('prevents quantity increases exceeding daily limit', function () {
    $product = Product::create([
        'quantity' => 10,
        'max_daily_increase' => 20,
    ]);
    
    $product->quantity = 50; // Increase of 40, exceeds limit
    
    $rule = new QuantityIncreaseRule();
    
    expect(fn() => $rule->validate($product))
        ->toThrow(BusinessRuleViolationException::class, 'exceeds daily limit');
});
```

### Testing Field Rule Sets

```php
<?php

use App\RuleSets\QuantityRuleSet;

it('only supports models with dirty quantity field', function () {
    $product = Product::create(['quantity' => 10]);
    $ruleSet = new QuantityRuleSet();
    
    expect($ruleSet->supports($product))->toBeFalse();
    
    $product->quantity = 20;
    expect($ruleSet->supports($product))->toBeTrue();
});

it('includes all quantity-related rules', function () {
    $ruleSet = new QuantityRuleSet();
    $rules = $ruleSet->getRules();
    
    expect($rules)->toHaveCount(3);
    expect($rules[0])->toBeInstanceOf(QuantityIncreaseRule::class);
});
```

### Testing Full Validation

```php
<?php

it('validates complete product updates', function () {
    $product = Product::create([
        'quantity' => 10,
        'price' => 50.00,
        'status' => 'draft',
    ]);
    
    $product->fill([
        'quantity' => 25,
        'price' => 45.00,
        'status' => 'pending',
    ]);
    
    $graph = ModelStateGraph::for(Product::class)
        ->addFieldRuleSet(new QuantityRuleSet())
        ->addFieldRuleSet(new PriceRuleSet())
        ->addFieldRuleSet(new StatusRuleSet());
    
    expect($graph->isValid($product))->toBeTrue();
});

it('catches invalid status transitions', function () {
    $product = Product::create(['status' => 'draft']);
    $product->status = 'shipped'; // Invalid: draft can't go directly to shipped
    
    $graph = ModelStateGraph::for(Product::class)
        ->addFieldRuleSet(new StatusRuleSet());
    
    expect($graph->isValid($product))->toBeFalse();
    expect($graph->getViolations($product))
        ->toContain('Cannot transition from \'draft\' to \'shipped\'');
});
```

## Best Practices

### 1. Keep Rules Focused and Single-Purpose

Each business rule should validate one specific concern:

```php
// Good: Focused rule
class QuantityMustBePositiveRule implements BusinessRule { ... }
class QuantityWithinRangeRule implements BusinessRule { ... }

// Bad: Rule doing too much
class QuantityValidationRule implements BusinessRule { ... } // validates everything
```

### 2. Use `supports()` Efficiently

Skip expensive validation when rules don't apply:

```php
public function supports(Product $model): bool
{
    // Quick check: only run when relevant
    if (!$model->isDirty('price')) {
        return false;
    }
    
    // More expensive checks only if needed
    return $model->category === 'premium';
}
```

### 3. Provide Clear, Actionable Violation Messages

Help users understand what went wrong and how to fix it:

```php
// Good: Clear and actionable
throw new BusinessRuleViolationException(
    "Quantity increase of {$increase} exceeds daily limit of {$limit}. Try again tomorrow or request approval."
);

// Bad: Vague
throw new BusinessRuleViolationException("Invalid quantity");
```

### 4. Organize Rules by Model

Since RuleSets and Rules are tied to specific models, organize them by model for better clarity and maintainability:

```php
app/
├── Models/
│   ├── Product.php
│   └── Order.php
└── BusinessRules/
    ├── Product/
    │   ├── Quantity/
    │   │   ├── QuantityRuleSet.php
    │   │   ├── QuantityIncreaseRule.php
    │   │   ├── QuantityDecreaseRule.php
    │   │   └── QuantityRangeRule.php
    │   ├── Price/
    │   │   ├── PriceRuleSet.php
    │   │   ├── PriceRangeRule.php
    │   │   └── PriceApprovalRule.php
    │   └── Status/
    │       ├── StatusRuleSet.php
    │       └── StatusTransitionRule.php
    └── Order/
        ├── Status/
        │   ├── StatusRuleSet.php
        │   └── OrderStatusTransitionRule.php
        └── Payment/
            ├── PaymentRuleSet.php
            └── PaymentValidationRule.php
```

This structure groups related rules by their field/concern, making it easy to find and maintain all rules for a specific field.

### 5. Leverage Dependency Injection

Use Laravel's container for flexibility and testability:

```php
class PriceRuleSet implements FieldRuleSet
{
    public function __construct(
        private PricingService $pricing,
        private ?User $user = null
    ) {
        $this->user ??= auth()->user();
    }
    
    public function getRules(): array
    {
        return [
            new PriceRangeRule($this->pricing),
            new PriceApprovalRule($this->user),
        ];
    }
}
```

### 6. Test Thoroughly

Write tests for:

- Individual rule logic
- Rule support conditions
- Complete validation scenarios
- Edge cases and error conditions

### 7. Document Your State Machines

When implementing complex status transitions, document them:

```php
/**
 * Order Status State Machine
 * 
 * draft → pending → approved → shipped → delivered
 *   ↓       ↓         ↓
 * cancelled
 * 
 * Business Rules:
 * - Orders can be cancelled at any stage before delivery
 * - Shipped orders require tracking number
 * - Approved orders require payment confirmation
 */
class StatusTransitionRule implements BusinessRule { ... }
```

## Performance Considerations

- The graph only runs rules for fields that have changed (`isDirty()`)
- Use the `supports()` method to skip expensive validation early
- Rules are evaluated lazily - validation stops at the first violation
- Consider caching expensive lookups within rules for the same request

## Comparison with Laravel Validation

Laravel Model State Graph complements Laravel's built-in validation but serves a different purpose:

| Feature               | Laravel Validation       | Model State Graph             |
|-----------------------|--------------------------|-------------------------------|
| **Use Case**          | Request input validation | Business logic validation     |
| **Context**           | HTTP layer               | Model layer                   |
| **State Awareness**   | Limited                  | Full state transition support |
| **Conditional Logic** | Basic                    | Complex, context-aware        |
| **Integration**       | Form Requests            | Model lifecycle events        |

Use both together: Laravel validation for input sanitization, Model State Graph for business rule enforcement.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

If you've found a bug regarding security please mail [theo.benoit16@gmail.com](mailto:theo.benoit16@gmail.com) instead of using the issue
tracker.

## Credits

- [Théo Benoit](https://github.com/Androlax2)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

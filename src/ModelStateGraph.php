<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph;

use Androlax\LaravelModelStateGraph\Contracts\FieldRuleSet;
use Androlax\LaravelModelStateGraph\Exceptions\BusinessRuleViolationException;
use Androlax\LaravelModelStateGraph\Exceptions\DuplicateFieldException;
use Androlax\LaravelModelStateGraph\Exceptions\FieldNotFoundException;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * @template T of Model
 */
final class ModelStateGraph
{
    /** @var array<string, FieldRuleSet<T>> */
    private array $fieldRuleSets = [];

    /**
     * @param  class-string<T>  $modelClass
     */
    public function __construct(private readonly string $modelClass)
    {
        throw_if(
            ! class_exists($this->modelClass) || ! is_subclass_of($this->modelClass, Model::class),
            new InvalidArgumentException("Class {$this->modelClass} is not a valid Eloquent model")
        );
    }

    /**
     * @param  class-string<T>  $modelClass
     * @return static<T>
     */
    public static function for(string $modelClass): self
    {
        return new self($modelClass);
    }

    /**
     * @param  FieldRuleSet<T>  $ruleSet
     * @return static<T>
     *
     * @throws FieldNotFoundException
     */
    public function addFieldRuleSet(FieldRuleSet $ruleSet): self
    {
        $field = $ruleSet->getField();

        $this->validateFieldExists($field);

        throw_if(array_key_exists($field, $this->fieldRuleSets), new DuplicateFieldException($field));

        $this->fieldRuleSets[$field] = $ruleSet;

        return $this;
    }

    /**
     * @param  T  $model
     */
    public function isValid(Model $model): bool
    {
        return $this->validate($model)->isValid();
    }

    /**
     * @param  T  $model
     * @return array<int, string>
     */
    public function getViolations(Model $model): array
    {
        return $this->validate($model)->getViolations();
    }

    /**
     * @param  T  $model
     */
    public function validate(Model $model): ValidationResult
    {
        $violations = [];

        foreach ($this->fieldRuleSets as $ruleSet) {
            if (! $ruleSet->supports($model)) {
                continue;
            }

            foreach ($ruleSet->getRules() as $rule) {
                if (! $rule->supports($model)) {
                    continue;
                }

                try {
                    $rule->validate($model);
                } catch (BusinessRuleViolationException $e) {
                    $violations[] = $e->getMessage();
                }
            }
        }

        return new ValidationResult($violations === [], $violations);
    }

    /**
     * @throws FieldNotFoundException
     */
    private function validateFieldExists(string $field): void
    {
        /** @var T $model */
        $model = new $this->modelClass();

        $table = $model->getTable();
        $schema = $model->getConnection()->getSchemaBuilder();

        $columns = $schema->getColumnListing($table);

        $hasColumn = in_array($field, $columns, true);
        $hasMethod = method_exists($model, $field);

        throw_if(! $hasColumn && ! $hasMethod, FieldNotFoundException::forField($field, $model));
    }
}

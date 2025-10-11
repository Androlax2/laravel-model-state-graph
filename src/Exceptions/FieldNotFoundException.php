<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Throwable;

final class FieldNotFoundException extends Exception
{
    private const MAX_SUGGESTIONS = 3;

    /**
     * @param  array<string>  $availableFields
     */
    public function __construct(
        string $field,
        string $modelClass,
        array $availableFields = [],
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message = $this->buildMessage($field, $modelClass, $availableFields);

        parent::__construct($message, $code, $previous);
    }

    public static function forField(string $field, Model $model): self
    {
        $modelClass = $model::class;
        $availableFields = array_keys($model->getAttributes());

        return new self($field, $modelClass, $availableFields);
    }

    public function getField(): string
    {
        preg_match("/Field '([^']+)'/", $this->getMessage(), $matches);

        return $matches[1] ?? '';
    }

    public function getModelClass(): string
    {
        preg_match("/model \[([^\]]+)\]/", $this->getMessage(), $matches);

        return $matches[1] ?? '';
    }

    /**
     * @param  array<string>  $availableFields
     */
    private function buildMessage(string $field, string $modelClass, array $availableFields): string
    {
        $baseMessage = sprintf("Field '%s' does not exist on model [%s].", $field, $modelClass);

        if ($availableFields === []) {
            return $baseMessage;
        }

        $suggestions = $this->getSuggestions($field, $availableFields);

        if ($suggestions !== []) {
            return sprintf('%s Did you mean: %s?', $baseMessage, implode(', ', $suggestions));
        }

        return sprintf('%s Available fields: %s', $baseMessage, implode(', ', $availableFields));
    }

    /**
     * @param  array<string>  $availableFields
     * @return array<string>
     */
    private function getSuggestions(string $field, array $availableFields): array
    {
        $suggestions = [];
        $fieldLower = mb_strtolower($field);

        foreach ($availableFields as $availableField) {
            $availableLower = mb_strtolower($availableField);

            // Simple Levenshtein distance for typo detection
            if (levenshtein($fieldLower, $availableLower) <= 2) {
                $suggestions[] = $availableField;
            }

            // Also check for partial matches
            if (
                str_contains($availableLower, $fieldLower) ||
                str_contains($fieldLower, $availableLower)
            ) {
                $suggestions[] = $availableField;
            }
        }

        return array_slice(array_unique($suggestions), 0, self::MAX_SUGGESTIONS);
    }
}

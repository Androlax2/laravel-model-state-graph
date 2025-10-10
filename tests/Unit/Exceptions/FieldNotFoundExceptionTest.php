<?php

declare(strict_types=1);

use Androlax\LaravelModelStateGraph\Exceptions\FieldNotFoundException;
use Tests\Fixtures\Models\TestModel;

it('creates exception with basic message', function (): void {
    $exception = new FieldNotFoundException('unknown_field', TestModel::class);

    expect($exception->getMessage())->toBe(
        "Field 'unknown_field' does not exist on model [Tests\Fixtures\Models\TestModel]."
    );
});

it('includes available fields in message', function (): void {
    $exception = new FieldNotFoundException(
        'unknown_field',
        TestModel::class,
        ['id', 'name', 'email']
    );

    expect($exception->getMessage())->toContain('Available fields: id, name, email');
});

it('suggests similar field names for typos', function (): void {
    $exception = new FieldNotFoundException(
        'emial',  // Typo of 'email'
        TestModel::class,
        ['id', 'name', 'email', 'created_at']
    );

    expect($exception->getMessage())->toContain('Did you mean: email?');
});

it('suggests multiple similar fields for partial matches', function (): void {
    $exception = new FieldNotFoundException(
        'nam',  // Partial match
        TestModel::class,
        ['id', 'name', 'username', 'first_name', 'created_at']
    );

    $message = $exception->getMessage();
    expect($message)->toContain('Did you mean:')
        ->and($message)->toContain('name')
        ->and($message)->toContain('username')
        ->and($message)->toContain('first_name');
});

it('limits suggestions to three fields maximum', function (): void {
    $exception = new FieldNotFoundException(
        'field',
        TestModel::class,
        ['field1', 'field2', 'field3', 'field4', 'field5']
    );

    $message = $exception->getMessage();
    $suggestionPart = explode('Did you mean: ', $message)[1] ?? '';
    $suggestions = explode(', ', rtrim($suggestionPart, '?'));

    expect($suggestions)->toHaveCount(3);
});

it('creates exception using factory method', function (): void {
    $model = new TestModel();
    $exception = FieldNotFoundException::forField('unknown_field', $model);

    expect($exception->getMessage())->toBe(
        "Field 'unknown_field' does not exist on model [Tests\Fixtures\Models\TestModel]."
    );
});

it('extracts field name from exception', function (): void {
    $exception = new FieldNotFoundException('test_field', TestModel::class);

    expect($exception->getField())->toBe('test_field');
});

it('extracts model class from exception', function (): void {
    $exception = new FieldNotFoundException('test_field', TestModel::class);

    expect($exception->getModelClass())->toBe(TestModel::class);
});

it('preserves code and previous exception', function (): void {
    $previous = new RuntimeException('Previous exception');
    $exception = new FieldNotFoundException('test_field', TestModel::class, [], 123, $previous);

    expect($exception->getCode())->toBe(123)
        ->and($exception->getPrevious())->toBe($previous);
});

it('handles empty available fields gracefully', function (): void {
    $exception = new FieldNotFoundException('test_field', TestModel::class, []);

    expect($exception->getMessage())->not->toContain('Available fields:')
        ->and($exception->getMessage())->not->toContain('Did you mean:');
});

it('detects various typo patterns using levenshtein', function ($typo, $correct): void {
    $exception = new FieldNotFoundException(
        $typo,
        TestModel::class,
        [$correct, 'other_field']
    );

    expect($exception->getMessage())->toContain($correct);
})->with([
    ['quantitty', 'quantity'],      // Double letter
    ['prize', 'price'],             // Common typo
    ['adress', 'address'],          // Missing letter
    ['creaated_at', 'created_at'],  // Extra letter
]);

it('handles case insensitive suggestions', function (): void {
    $exception = new FieldNotFoundException(
        'EMAIL',  // Uppercase
        TestModel::class,
        ['email', 'name']  // Lowercase
    );

    expect($exception->getMessage())->toContain('email');
});

it('prioritizes exact partial matches over distant ones', function (): void {
    $exception = new FieldNotFoundException(
        'user',
        TestModel::class,
        ['username', 'user_id', 'created_by', 'email']
    );

    $message = $exception->getMessage();
    expect($message)->toContain('username')
        ->and($message)->toContain('user_id')
        ->and($message)->not->toContain('created_by'); // Not similar enough
});

<?php

declare(strict_types=1);

namespace Androlax\LaravelModelStateGraph\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class TestModel extends Model
{
    use HasFactory;

    protected $guarded = [];
}

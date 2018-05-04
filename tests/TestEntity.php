<?php

namespace Saritasa\LaravelEntityServices\Tests;

use Illuminate\Database\Eloquent\Model;

/**
 * Entity for tests.
 *
 * @property string $field1
 * @property string $field2
 * @property string $field3
 */
class TestEntity extends Model
{
    public const FIELD_1 = 'field1';
    public const FIELD_2 = 'field2';
    public const FIELD_3 = 'field3';

    protected $fillable = [
        self::FIELD_1,
        self::FIELD_2,
        self::FIELD_3,
    ];
}

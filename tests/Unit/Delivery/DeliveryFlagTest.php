<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery;

use App\Delivery\DeliveryFlag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DeliveryFlagTest extends TestCase
{
    #[DataProvider('falseValues')]
    public function test_common_false_values_fail_closed(mixed $value): void
    {
        $this->assertFalse(DeliveryFlag::parse($value));
    }

    #[DataProvider('trueValues')]
    public function test_common_true_values_are_enabled(mixed $value): void
    {
        $this->assertTrue(DeliveryFlag::parse($value));
    }

    public static function falseValues(): array
    {
        return [
            'boolean false' => [false],
            'integer zero' => [0],
            'false' => ['false'],
            'zero' => ['0'],
            'off' => ['off'],
            'no' => ['no'],
            'missing' => [null],
            'invalid' => ['not-a-boolean'],
        ];
    }

    public static function trueValues(): array
    {
        return [
            'boolean true' => [true],
            'integer one' => [1],
            'true' => ['true'],
            'one' => ['1'],
            'on' => ['on'],
            'yes' => ['yes'],
        ];
    }
}

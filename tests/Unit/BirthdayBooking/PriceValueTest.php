<?php

declare(strict_types=1);

namespace Tests\Unit\BirthdayBooking;

use Abandon\Birthday\Models\PriceValue;
use Abandon\Birthday\Rules\BirthdayPrice;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Tests\TestCase;

final class PriceValueTest extends TestCase
{
    public function test_it_converts_cad_decimal_strings_to_minor_units_without_float_math(): void
    {
        foreach ([
            '0' => 0,
            '0.01' => 1,
            '250' => 25000,
            '250.5' => 25050,
            '250.50' => 25050,
            ' 250.50 ' => 25050,
            PriceValue::MAX_AMOUNT => PriceValue::MAX_MINOR_UNITS,
        ] as $value => $expected) {
            $this->assertSame($expected, PriceValue::toMinorUnits($value));
        }
    }

    public function test_it_rejects_invalid_and_oversized_values(): void
    {
        foreach ([
            '42949672.96',
            str_repeat('9', 200),
            '1e2',
            '-1',
            '1.234',
            '1,000.00',
            '',
            'letters',
        ] as $value) {
            try {
                PriceValue::toMinorUnits($value);
                $this->fail('Expected price validation to fail for '.$value);
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_shared_validation_rule_returns_a_price_field_error_without_exposing_an_exception(): void
    {
        foreach (['42949672.96', str_repeat('9', 200), '1e2', '-1', '1.234', '', 'letters'] as $price) {
            $validator = Validator::make(['price' => $price], ['price' => ['required', new BirthdayPrice]]);

            $this->assertTrue($validator->fails());
            $this->assertArrayHasKey('price', $validator->errors()->toArray());
            $this->assertStringNotContainsString('InvalidArgumentException', $validator->errors()->first('price'));
            $this->assertStringNotContainsString('SQL', $validator->errors()->first('price'));
        }
    }

    public function test_shared_validation_rule_accepts_the_exact_maximum(): void
    {
        $validator = Validator::make(
            ['price' => PriceValue::MAX_AMOUNT],
            ['price' => ['required', new BirthdayPrice]],
        );

        $this->assertFalse($validator->fails());
    }

    public function test_minor_unit_addition_and_display_do_not_use_float_math(): void
    {
        $this->assertSame(28575, PriceValue::addMinorUnits(25000, 1550, 2025));
        $this->assertSame(0, PriceValue::addMinorUnits());
        $this->assertSame('250.00', PriceValue::formatMinorUnits(25000));
        $this->assertSame('$250.00', PriceValue::formatCad(25000));
        $this->assertSame('$0.01', PriceValue::formatCad(1));
    }

    public function test_minor_unit_addition_rejects_negative_values_and_overflow(): void
    {
        foreach ([[-1], [PHP_INT_MAX, 1]] as $values) {
            try {
                PriceValue::addMinorUnits(...$values);
                $this->fail('Expected minor-unit addition to reject the values.');
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }
}

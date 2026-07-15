<?php

declare(strict_types=1);

namespace Tests\Unit\BirthdayBooking;

use Abandon\Birthday\Models\PriceValue;
use Abandon\Birthday\Models\BirthdayAddon;
use Abandon\Birthday\Models\BirthdayPackage;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PriceValueTest extends TestCase
{
    public function test_it_converts_cad_decimal_strings_to_minor_units_without_float_math(): void
    {
        $this->assertSame(0, PriceValue::toMinorUnits('0'));
        $this->assertSame(25000, PriceValue::toMinorUnits('250'));
        $this->assertSame(25050, PriceValue::toMinorUnits('250.5'));
        $this->assertSame(25099, PriceValue::toMinorUnits('250.99'));
    }

    public function test_it_rejects_negative_non_numeric_and_over_precision_values(): void
    {
        foreach (['-1', '1.234', 'abc', '', '1e2'] as $value) {
            try {
                PriceValue::toMinorUnits($value);
                $this->fail('Expected price validation to fail for '.$value);
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_catalog_models_expose_decimal_price_and_package_items_text(): void
    {
        $package = new BirthdayPackage;
        $package->price = '125.50';
        $package->included_items_text = "Private room\nBirthday cake table";

        $this->assertSame(12550, $package->price_minor);
        $this->assertSame('125.50', $package->price);
        $this->assertSame(['Private room', 'Birthday cake table'], $package->included_items);

        $addon = new BirthdayAddon;
        $addon->price = '15.00';

        $this->assertSame(1500, $addon->price_minor);
        $this->assertSame('15.00', $addon->price);
    }
}

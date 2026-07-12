<?php

declare(strict_types=1);

namespace Tests\Unit\BirthdayBooking;

use App\BirthdayBooking\BirthdayTelephone;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class BirthdayTelephoneTest extends TestCase
{
    public function test_it_normalizes_common_canada_and_us_formats(): void
    {
        $telephone = new BirthdayTelephone;

        foreach ([
            '5145550100',
            '514-555-0100',
            '(514) 555-0100',
            '+1 514 555 0100',
            '+15145550100',
            '1 514 555 0100',
        ] as $value) {
            $this->assertSame('+15145550100', $telephone->normalize($value));
        }
    }

    public function test_it_rejects_invalid_or_non_north_american_numbers(): void
    {
        $telephone = new BirthdayTelephone;

        foreach (['514555010', '51455501001', 'abc5145550100', '+445145550100', '0000000000'] as $value) {
            try {
                $telephone->normalize($value);
                $this->fail('Expected telephone validation to fail for '.$value);
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('form.telephone', $exception->errors());
            }
        }
    }

    public function test_empty_telephone_preserves_the_existing_optional_behavior(): void
    {
        $this->assertNull((new BirthdayTelephone)->normalize(null));
        $this->assertNull((new BirthdayTelephone)->normalize(''));
    }
}

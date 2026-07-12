<?php

declare(strict_types=1);

namespace App\BirthdayBooking;

use Illuminate\Validation\ValidationException;

final class BirthdayTelephone
{
    public function normalize(?string $telephone): ?string
    {
        $value = trim((string) $telephone);

        if ($value === '') {
            return null;
        }

        if (! preg_match('/^\+?[0-9\s()\-]+$/', $value)) {
            $this->invalid();
        }

        $compact = preg_replace('/[\s()\-]+/', '', $value);

        if (! is_string($compact)) {
            $this->invalid();
        }

        if (str_starts_with($compact, '+1')) {
            $national = substr($compact, 2);
        } elseif (str_starts_with($compact, '1') && strlen($compact) === 11) {
            $national = substr($compact, 1);
        } elseif (strlen($compact) === 10) {
            $national = $compact;
        } else {
            $this->invalid();
        }

        if (! preg_match('/^[2-9]\d{2}[2-9]\d{6}$/', $national)) {
            $this->invalid();
        }

        return '+1'.$national;
    }

    private function invalid(): never
    {
        throw ValidationException::withMessages([
            'form.telephone' => trans('birthday_booking.telephone_invalid'),
        ]);
    }
}

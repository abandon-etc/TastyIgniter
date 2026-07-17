<?php

declare(strict_types=1);

namespace Abandon\Birthday\Services;

use Abandon\Birthday\Models\BirthdayBooking;
use Abandon\Birthday\Models\BirthdayBookingAddon;
use Abandon\Birthday\Models\BirthdayBookingStatus;
use App\BirthdayBooking\BirthdayRules;
use App\BirthdayBooking\BirthdaySlot;
use App\BirthdayBooking\BirthdayTelephone;
use Carbon\CarbonImmutable;
use Igniter\Local\Models\Location;
use Igniter\User\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class BirthdayBookingService
{
    public function __construct(
        private readonly BirthdayRules $rules,
        private readonly BirthdayTelephone $telephone,
        private readonly BirthdayPricingSnapshotService $pricing,
    ) {}

    /**
     * @param  array<int, int|string>  $addonIds
     * @param  array{first_name?: mixed, last_name?: mixed, email?: mixed, telephone?: mixed}  $contact
     */
    public function createCatalogPricedBooking(
        Customer $customer,
        Location $location,
        string $eventDate,
        string $slotCode,
        int $guestCount,
        array $addonIds = [],
        array $contact = [],
        ?CarbonImmutable $now = null,
    ): BirthdayBooking {
        return DB::transaction(function () use (
            $customer,
            $location,
            $eventDate,
            $slotCode,
            $guestCount,
            $addonIds,
            $contact,
            $now,
        ): BirthdayBooking {
            $persistedCustomer = Customer::query()->find($customer->getKey());
            $persistedLocation = Location::query()->find($location->getKey());

            if (! $persistedCustomer) {
                throw ValidationException::withMessages([
                    'customer' => trans('abandon.birthday::default.booking_errors.customer_required'),
                ]);
            }

            if (! $persistedLocation) {
                throw ValidationException::withMessages([
                    'location' => trans('abandon.birthday::default.booking_errors.location_required'),
                ]);
            }

            $date = $this->rules->normalizeDate($eventDate, $now);
            $slot = $this->resolveSlot($slotCode);
            $this->validateGuestCount($guestCount);
            $validatedContact = $this->validatedContact($persistedCustomer, $contact);
            $snapshot = $this->pricing->capture($addonIds);
            [$startsAt, $endsAt] = $this->utcSlotTimes($date, $slot);

            $booking = BirthdayBooking::query()->create([
                'customer_id' => $persistedCustomer->getKey(),
                'location_id' => $persistedLocation->getKey(),
                'event_date' => $date->toDateString(),
                'slot_code' => $slot->code,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'timezone' => $this->rules->timezone(),
                'guest_count' => $guestCount,
                'status' => BirthdayBookingStatus::CATALOG_PRICED,
                'currency' => $snapshot->currency,
                'package_id' => $snapshot->packageId,
                'package_name_snapshot' => $snapshot->packageName,
                'package_description_snapshot' => $snapshot->packageDescription,
                'package_included_items_snapshot' => $snapshot->packageIncludedItems,
                'package_price_minor_snapshot' => $snapshot->packagePriceMinor,
                'addons_subtotal_minor' => $snapshot->addonsSubtotalMinor,
                'catalog_subtotal_minor' => $snapshot->catalogSubtotalMinor,
                'contact_first_name_snapshot' => $validatedContact['first_name'],
                'contact_last_name_snapshot' => $validatedContact['last_name'],
                'contact_email_snapshot' => $validatedContact['email'],
                'contact_telephone_snapshot' => $validatedContact['telephone'],
                'pricing_version' => BirthdayBooking::PRICING_VERSION,
                'priced_at' => CarbonImmutable::now('UTC'),
            ]);

            foreach ($snapshot->addons as $addon) {
                BirthdayBookingAddon::query()->create([
                    'birthday_booking_id' => $booking->getKey(),
                    'addon_id' => $addon['id'],
                    'addon_name_snapshot' => $addon['name'],
                    'addon_description_snapshot' => $addon['description'],
                    'addon_price_minor_snapshot' => $addon['price_minor'],
                    'sort_order_snapshot' => $addon['sort_order'],
                ]);
            }

            return $booking->load(['customer', 'location', 'addons']);
        }, 3);
    }

    public function findByPublicId(string $publicId): BirthdayBooking
    {
        return BirthdayBooking::query()
            ->with(['customer', 'location', 'addons'])
            ->where('public_id', $publicId)
            ->firstOrFail();
    }

    public function cancel(BirthdayBooking $booking, ?CarbonImmutable $cancelledAt = null): BirthdayBooking
    {
        if ($booking->status === BirthdayBookingStatus::CANCELLED) {
            return $booking;
        }

        $booking->status = BirthdayBookingStatus::CANCELLED;
        $booking->cancelled_at = $cancelledAt ?? CarbonImmutable::now('UTC');
        $booking->save();

        return $booking->fresh(['customer', 'location', 'addons']);
    }

    private function resolveSlot(string $slotCode): BirthdaySlot
    {
        try {
            return BirthdaySlot::find($slotCode);
        } catch (\InvalidArgumentException) {
            throw ValidationException::withMessages([
                'slot' => trans('birthday_booking.invalid_slot'),
            ]);
        }
    }

    private function validateGuestCount(int $guestCount): void
    {
        if ($guestCount < 1 || $guestCount > 999) {
            throw ValidationException::withMessages([
                'guest_count' => trans('abandon.birthday::default.booking_errors.guest_count'),
            ]);
        }
    }

    /**
     * @param  array{first_name?: mixed, last_name?: mixed, email?: mixed, telephone?: mixed}  $contact
     * @return array{first_name: string, last_name: string, email: string, telephone: ?string}
     */
    private function validatedContact(Customer $customer, array $contact): array
    {
        $values = [
            'first_name' => $contact['first_name'] ?? $customer->first_name,
            'last_name' => $contact['last_name'] ?? $customer->last_name,
            'email' => $contact['email'] ?? $customer->email,
            'telephone' => $contact['telephone'] ?? $customer->telephone,
        ];

        $validated = Validator::make($values, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:32'],
        ])->validate();

        try {
            $telephone = $this->telephone->normalize($validated['telephone'] ?? null);
        } catch (ValidationException) {
            throw ValidationException::withMessages([
                'contact.telephone' => trans('birthday_booking.telephone_invalid'),
            ]);
        }

        return [
            'first_name' => trim($validated['first_name']),
            'last_name' => trim($validated['last_name']),
            'email' => strtolower(trim($validated['email'])),
            'telephone' => $telephone,
        ];
    }

    /**
     * @return array{CarbonImmutable, CarbonImmutable}
     */
    private function utcSlotTimes(CarbonImmutable $date, BirthdaySlot $slot): array
    {
        [$startHour, $startMinute] = array_map('intval', explode(':', $slot->start));
        [$endHour, $endMinute] = array_map('intval', explode(':', $slot->end));
        $startsAt = $date->setTime($startHour, $startMinute);
        $endsAt = $date->setTime($endHour, $endMinute);

        if ($endsAt->lessThanOrEqualTo($startsAt)) {
            $endsAt = $endsAt->addDay();
        }

        return [$startsAt->setTimezone('UTC'), $endsAt->setTimezone('UTC')];
    }
}

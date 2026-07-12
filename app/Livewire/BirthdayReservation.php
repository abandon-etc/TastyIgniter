<?php

namespace App\Livewire;

use App\BirthdayBooking\BirthdayAvailabilityService;
use App\BirthdayBooking\BirthdayRules;
use App\BirthdayBooking\BirthdayTelephone;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Local\Facades\Location;
use Igniter\Orange\Livewire\Forms\BookingForm;
use Igniter\Reservation\Classes\BookingManager;
use Igniter\User\Facades\Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

final class BirthdayReservation extends Component
{
    public const string STEP_PICKER = 'picker';

    public const string STEP_BOOKING = 'booking';

    public BookingForm $form;

    public string $date;

    public int $guest = 1;

    public ?string $selectedSlot = null;

    public string $step = self::STEP_PICKER;

    protected BookingManager $manager;

    public function mount(BirthdayRules $rules): void
    {
        [$earliest] = $rules->dateWindow();
        $this->date = $earliest->toDateString();

        if ($customer = Auth::customer()) {
            $this->form->firstName = $customer->first_name;
            $this->form->lastName = $customer->last_name;
            $this->form->email = $customer->email;
            $this->form->telephone = $customer->telephone;
        }
    }

    public function boot(): void
    {
        $this->manager = resolve(BookingManager::class);
        $this->manager->useLocation(Location::current());
    }

    public function updatedDate(): void
    {
        $this->selectedSlot = null;
        $this->step = self::STEP_PICKER;
        $this->resetErrorBag();
    }

    public function selectSlot(string $slot): void
    {
        $location = Location::currentOrDefault();
        $availability = app(BirthdayAvailabilityService::class);
        $availability->assertDateAndSlot($this->date, $slot);

        $available = collect($availability->forDate($location->location_id, $this->date))
            ->firstWhere('code', $slot);

        if (! $available || ! $available['available']) {
            $this->addError('slot', trans('birthday_booking.slot_unavailable'));

            return;
        }

        $this->selectedSlot = $slot;
        $this->step = self::STEP_BOOKING;
    }

    public function onComplete(): void
    {
        if (! $this->selectedSlot) {
            $this->addError('slot', trans('birthday_booking.invalid_slot'));

            return;
        }

        $this->validate([
            'guest' => ['required', 'integer', 'min:1'],
        ]);
        $this->form->telephone = app(BirthdayTelephone::class)->normalize($this->form->telephone);
        $this->form->validate();
        $slot = app(BirthdayAvailabilityService::class)->assertDateAndSlot(
            $this->date,
            $this->selectedSlot,
        );

        $reservation = $this->manager->loadReservation();
        $reservation->birthday_booking = true;
        $data = [
            'sdateTime' => $this->date.' '.$slot->start,
            'guest' => $this->guest,
            'first_name' => $this->form->firstName,
            'last_name' => $this->form->lastName,
            'email' => Auth::customer()?->email ?? $this->form->email,
            'telephone' => $this->form->telephone ?? Auth::customer()?->telephone ?? '',
            'comment' => $this->form->comment,
        ];

        try {
            DB::transaction(function () use ($reservation, $data): void {
                $this->manager->saveReservation($reservation, $data);
            });
        } catch (QueryException $exception) {
            if ($this->isSlotConflict($exception)) {
                throw ValidationException::withMessages([
                    'slot' => trans('birthday_booking.slot_unavailable'),
                ]);
            }

            throw $exception;
        } catch (ApplicationException $exception) {
            $this->dispatch(
                'birthday-booking::alert',
                message: trans('birthday_booking.process_failed'),
                exception: $exception->getMessage(),
            );

            return;
        }

        $this->reset();

        $this->redirect(page_url('reservation.success', [
            'hash' => $reservation->hash,
            'location' => Location::current()->permalink_slug,
        ]));
    }

    public function render(BirthdayRules $rules, BirthdayAvailabilityService $availability): View
    {
        $location = Location::currentOrDefault();
        [$earliest, $latest] = $rules->dateWindow();

        try {
            $slots = $availability->forDate($location->location_id, $this->date);
        } catch (ValidationException) {
            $slots = [];
        }

        return view('livewire.birthday-reservation', [
            'customer' => Auth::customer(),
            'slots' => $slots,
            'earliestDate' => $earliest->toDateString(),
            'latestDate' => $latest->toDateString(),
        ]);
    }

    private function isSlotConflict(QueryException $exception): bool
    {
        return BirthdayAvailabilityService::isSlotConflict($exception);
    }
}

<div>
    @if($step === \App\Livewire\BirthdayReservation::STEP_BOOKING)
        <div class="card mb-1 bg-white">
            <div class="card-body">
                {!! $customer
                    ? sprintf(lang('igniter.orange::default.text_logged_out'), e($customer->first_name), url('logout'))
                    : sprintf(lang('igniter.orange::default.text_logged_in'), page_url('account.login'))
                !!}
            </div>
        </div>
        <div class="card bg-white">
            <div class="card-body">
                <div class="form-row mb-4">
                    <div class="col-sm-4">
                        <h5 class="text-muted">@lang('igniter.reservation::default.label_guest_num')</h5>
                        <h4 class="font-weight-normal">{{ $guest }}</h4>
                    </div>
                    <div class="col-sm-4">
                        <h5 class="text-muted">@lang('igniter.reservation::default.label_date')</h5>
                        <h4 class="font-weight-normal">{{ $date }}</h4>
                    </div>
                    <div class="col-sm-4">
                        <h5 class="text-muted">@lang('igniter.reservation::default.label_time')</h5>
                        <h4 class="font-weight-normal">
                            {{ trans('birthday_booking.slots.'.str_replace('-', '_', $selectedSlot)) }}
                        </h4>
                    </div>
                </div>
                @include('livewire.birthday-booking-form')
            </div>
        </div>
        <div class="card bg-white mt-1">
            <div class="card-body">
                <button type="button" class="btn btn-link px-0" wire:click="$set('step', 'picker')">
                    @lang('birthday_booking.change_selection')
                </button>
            </div>
        </div>
    @else
        <div class="card bg-white">
            <div class="card-body">
                <div class="mb-4">
                    <label for="birthday-booking-date" class="form-label">
                        @lang('birthday_booking.date')
                    </label>
                    <input
                        id="birthday-booking-date"
                        type="date"
                        class="form-control @error('date') is-invalid @enderror"
                        wire:model.live="date"
                        min="{{ $earliestDate }}"
                        max="{{ $latestDate }}"
                    >
                    @error('date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="birthday-booking-guest" class="form-label">
                        @lang('igniter.reservation::default.label_guest_num')
                    </label>
                    <input
                        id="birthday-booking-guest"
                        type="number"
                        class="form-control @error('guest') is-invalid @enderror"
                        wire:model.live="guest"
                        min="1"
                    >
                    <div class="form-text">@lang('birthday_booking.guest_note')</div>
                    @error('guest')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <fieldset>
                    <legend class="h5">@lang('birthday_booking.slot')</legend>
                    <div class="d-grid gap-2">
                        @foreach($slots as $slot)
                            <button
                                type="button"
                                class="btn {{ $selectedSlot === $slot['code'] ? 'btn-primary' : 'btn-outline-primary' }}"
                                wire:click="selectSlot('{{ $slot['code'] }}')"
                                @disabled(!$slot['available'])
                            >
                                {{ trans($slot['label']) }}
                                @unless($slot['available'])
                                    <span class="ms-1">(@lang('birthday_booking.unavailable'))</span>
                                @endunless
                            </button>
                        @endforeach
                    </div>
                </fieldset>

                @if($slots && collect($slots)->every(fn($slot) => ! $slot['available']))
                    <div class="alert alert-warning mt-3 mb-0">
                        @lang('birthday_booking.no_slots')
                    </div>
                @endif
                @error('slot')
                    <div class="alert alert-warning mt-3 mb-0">{{ $message }}</div>
                @enderror
            </div>
        </div>
    @endif
</div>

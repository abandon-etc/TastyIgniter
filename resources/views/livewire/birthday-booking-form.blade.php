<x-igniter-orange::forms.form id="birthday-booking-form" wire:submit="onComplete" novalidate>
    <div class="row g-3 mb-3">
        <div class="col-sm-6">
            <div @class(['form-floating', 'is-invalid' => has_form_error('form.firstName')])>
                <input
                    wire:model="form.firstName"
                    type="text"
                    id="birthday-first-name"
                    @class(['form-control', 'is-invalid' => has_form_error('form.firstName')])
                    placeholder="@lang('igniter.reservation::default.label_first_name')"
                    aria-describedby="birthdayFirstNameFeedback"
                    required
                />
                <label for="birthday-first-name">@lang('igniter.reservation::default.label_first_name')</label>
            </div>
            <x-igniter-orange::forms.error field="form.firstName" id="birthdayFirstNameFeedback" class="text-danger" />
        </div>
        <div class="col-sm-6">
            <div @class(['form-floating', 'is-invalid' => has_form_error('form.lastName')])>
                <input
                    wire:model="form.lastName"
                    type="text"
                    id="birthday-last-name"
                    @class(['form-control', 'is-invalid' => has_form_error('form.lastName')])
                    placeholder="@lang('igniter.reservation::default.label_last_name')"
                    aria-describedby="birthdayLastNameFeedback"
                    required
                />
                <label for="birthday-last-name">@lang('igniter.reservation::default.label_last_name')</label>
            </div>
            <x-igniter-orange::forms.error field="form.lastName" id="birthdayLastNameFeedback" class="text-danger" />
        </div>
        <div class="col-sm-6">
            <div @class(['form-floating', 'is-invalid' => has_form_error('form.email')])>
                <input
                    wire:model="form.email"
                    type="text"
                    id="birthday-email"
                    @class(['form-control', 'is-invalid' => has_form_error('form.email')])
                    @if($customer) readonly="readonly" @endif
                    placeholder="@lang('igniter.reservation::default.label_email')"
                    aria-describedby="birthdayEmailFeedback"
                    required
                />
                <label for="birthday-email">@lang('igniter.reservation::default.label_email')</label>
            </div>
            <x-igniter-orange::forms.error field="form.email" id="birthdayEmailFeedback" class="text-danger" />
        </div>
        <div class="col-sm-6">
            <div @class(['form-floating', 'is-invalid' => has_form_error('form.telephone')])>
                <input
                    wire:model="form.telephone"
                    type="tel"
                    id="birthday-telephone"
                    class="form-control @error('form.telephone') is-invalid @enderror"
                    inputmode="tel"
                    autocomplete="tel"
                    placeholder="@lang('birthday_booking.telephone_placeholder')"
                    aria-describedby="birthdayTelephoneFeedback"
                />
                <label for="birthday-telephone">@lang('igniter.reservation::default.label_telephone')</label>
            </div>
            <div id="birthdayTelephoneClientFeedback" class="invalid-feedback" hidden></div>
            <x-igniter-orange::forms.error field="form.telephone" id="birthdayTelephoneFeedback" class="text-danger" />
        </div>
    </div>

    <div class="mb-4">
        <div @class(['form-floating', 'is-invalid' => has_form_error('form.comment')])>
            <textarea
                wire:model="form.comment"
                id="birthday-comment"
                rows="4"
                @class(['form-control', 'is-invalid' => has_form_error('form.comment')])
                placeholder="@lang('igniter.reservation::default.label_comment')"
                aria-describedby="birthdayCommentFeedback"
            ></textarea>
            <label for="birthday-comment">@lang('igniter.reservation::default.label_comment')</label>
        </div>
        <x-igniter-orange::forms.error field="form.comment" id="birthdayCommentFeedback" class="text-danger" />
    </div>

    <button type="submit" class="btn btn-primary w-100 btn-lg">
        @lang('igniter.reservation::default.button_reservation')
    </button>
</x-igniter-orange::forms.form>

@script
<script>
    (() => {
        const input = document.getElementById('birthday-telephone');
        const form = input?.closest('form');
        const feedback = document.getElementById('birthdayTelephoneClientFeedback');
        const invalidMessage = @json(trans('birthday_booking.telephone_invalid'));

        if (!input || !form || input.dataset.birthdayTelephoneBound) {
            return;
        }

        input.dataset.birthdayTelephoneBound = 'true';
        const normalize = (value) => {
            const trimmed = value.trim();

            if (!trimmed) {
                return '';
            }

            if (!/^\+?[0-9\s()-]+$/.test(trimmed)) {
                return null;
            }

            const compact = trimmed.replace(/[\s()-]+/g, '');

            if (/^\+1\d{10}$/.test(compact)) {
                return compact;
            }

            if (/^1\d{10}$/.test(compact)) {
                return `+${compact}`;
            }

            if (/^\d{10}$/.test(compact)) {
                return `+1${compact}`;
            }

            return null;
        };

        const isValidNorthAmericanNumber = (value) => {
            const normalized = normalize(value);

            return normalized === '' || (
                normalized !== null &&
                /^\+1[2-9]\d{2}[2-9]\d{6}$/.test(normalized)
            );
        };

        const validate = () => {
            const valid = isValidNorthAmericanNumber(input.value);

            input.setCustomValidity(valid ? '' : invalidMessage);
            feedback.hidden = valid;
            feedback.textContent = valid ? '' : invalidMessage;
            input.classList.toggle('is-invalid', !valid);

            return valid;
        };

        input.addEventListener('blur', () => {
            const normalized = normalize(input.value);

            if (normalized !== null && normalized !== input.value) {
                input.value = normalized;
                input.dispatchEvent(new Event('input', {bubbles: true}));
            }

            validate();
        });
        form.addEventListener('submit', (event) => {
            if (!validate()) {
                event.preventDefault();
                input.focus();
            }
        });
    })();
</script>
@endscript

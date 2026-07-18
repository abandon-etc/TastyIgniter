<section class="mb-4" aria-labelledby="delivery-search-title">
    <h2 id="delivery-search-title" class="h5 mb-3">@lang('delivery.delivery')</h2>

    <div id="local-search-container">
        <div id="local-search-form">
            <x-igniter-orange::forms.form id="location-search" wire:submit="onSearchNearby">
                <div class="input-group input-group-lg bg-white rounded border p-1 mb-3">
                    <button
                        type="button"
                        data-control="user-position"
                        class="btn rounded border-0 shadow-none"
                        aria-label="@lang('delivery.use_current_location')"
                        wire:loading.class="disabled"
                    ><i class="fa fa-location-arrow fs-4 align-bottom" aria-hidden="true"></i></button>
                    <label class="visually-hidden" for="delivery-search-query">
                        @lang('delivery.enter_delivery_address')
                    </label>
                    <input
                        @if($searchAutocompleteEnabled)
                            wire:model.live.debounce.500ms="searchQuery"
                        @else
                            wire:model="searchQuery"
                        @endif
                        type="text"
                        id="delivery-search-query"
                        class="bg-white form-control shadow-none border-none"
                        placeholder="@lang('delivery.enter_delivery_address')"
                        autocomplete="street-address"
                    />
                    <button
                        type="submit"
                        class="btn btn-primary btn-lg fw-bold ms-lg-2 rounded"
                        data-control="search-local"
                        wire:loading.class="disabled"
                    >@lang('delivery.check_delivery_availability')</button>
                </div>
            </x-igniter-orange::forms.form>
        </div>

        @if($isSearching && $searchAutocompleteEnabled)
            @include('igniter-orange::includes.local.autocomplete-suggestions')
        @endif

        <x-igniter-orange::forms.error
            field="searchQuery"
            id="searchQueryFeedback"
            class="p-2 text-danger fw-bold"
        />

        @include('igniter-orange::includes.local.saved-address-picker')
    </div>
</section>

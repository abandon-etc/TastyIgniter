@php
    $location = \Igniter\Local\Facades\Location::currentOrDefault();
    $locationParams = $location ? ['location' => $location->permalink_slug] : [];
@endphp

<div class="home-cta-card text-center">
    <h1 id="single-store-home-title" class="home-cta-title mb-4">@lang('igniter.orange::default.home_cta_title')</h1>

    <div class="home-cta-actions d-grid gap-3 d-sm-flex justify-content-sm-center">
        <a
            class="btn btn-primary btn-lg fw-bold"
            href="{{ page_url('local.menus', $locationParams) }}"
        >@lang('igniter.orange::default.home_cta_order_button')</a>

        <a
            class="btn btn-outline-primary btn-lg fw-bold"
            href="{{ page_url('reservation.reservation', $locationParams) }}"
        >@lang('igniter.orange::default.home_cta_party_button')</a>
    </div>
</div>

@php
    $location = \Igniter\Local\Facades\Location::currentOrDefault();
    $locationParams = $location ? ['location' => $location->permalink_slug] : [];
    $deliveryGate = resolve(\App\Delivery\DeliveryAvailabilityGate::class);
    $deliveryAvailable = $deliveryGate->isEnabledForLocation($location);
    $collectionAvailable = $deliveryGate->isCollectionEnabled($location);
@endphp

<div class="home-cta-card text-center">
    <h1 id="single-store-home-title" class="home-cta-title mb-4">
        @lang($deliveryAvailable ? 'delivery.home_title_delivery' : 'delivery.home_title_pickup')
    </h1>

    @if($deliveryAvailable)
        @include('delivery.storefront.address-search')
    @elseif(!$collectionAvailable)
        <p class="alert alert-info" role="status">@lang('delivery.no_fulfillment_available')</p>
    @endif

    <div class="home-cta-actions d-grid gap-3 d-sm-flex justify-content-sm-center">
        @if($collectionAvailable)
            <a
                class="btn btn-primary btn-lg fw-bold"
                href="{{ page_url('local.menus', $locationParams) }}"
            >@lang('delivery.order_for_pickup')</a>
        @endif

        <a
            class="btn btn-outline-primary btn-lg fw-bold"
            href="{{ page_url('reservation.reservation', $locationParams) }}"
        >@lang('delivery.book_birthday_venue')</a>
    </div>
</div>

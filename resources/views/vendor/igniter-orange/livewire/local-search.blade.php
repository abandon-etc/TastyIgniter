@php
    $location = \Igniter\Local\Facades\Location::currentOrDefault();
    $locationParams = $location ? ['location' => $location->permalink_slug] : [];
@endphp

<div class="text-center">
    <h2 class="mb-3">Commandez en ligne pour cueillette ou réservez votre fête d’anniversaire.</h2>
    <p class="lead mb-4">
        Order online for pickup or book your birthday party.
    </p>

    <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
        <a
            class="btn btn-primary btn-lg fw-bold"
            href="{{ page_url('local.menus', $locationParams) }}"
        >Commander / Order Now</a>

        <a
            class="btn btn-outline-primary btn-lg fw-bold"
            href="{{ page_url('reservation.reservation', $locationParams) }}"
        >Réserver une fête / Book a Party</a>
    </div>
</div>

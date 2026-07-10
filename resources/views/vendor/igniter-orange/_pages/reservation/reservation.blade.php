---
title: igniter.orange::default.reservation_title
layout: default
permalink: ':location/reservation'
---
<div class="container pt-4 pb-5">
    <div class="card mb-1 bg-white">
        <div class="card-body">
            <x-igniter-orange::local-header current-page="reservation.reservation" />
        </div>
    </div>

    @if(config('birthday_booking.enabled'))
        <livewire:birthday-reservation />
    @else
        <livewire:igniter-orange::booking />
    @endif
</div>

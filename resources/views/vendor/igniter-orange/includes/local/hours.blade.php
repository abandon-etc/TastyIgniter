@php
    $currentLocation = \Igniter\Local\Facades\Location::currentOrDefault();
    $deliveryGate = resolve(\App\Delivery\DeliveryAvailabilityGate::class);
    $scheduleTypes = $deliveryGate->availableScheduleTypes(
        $currentLocation,
        collect($locationInfo->scheduleTypes()),
    );
    $scheduleItems = $deliveryGate->availableScheduleTypes(
        $currentLocation,
        collect($locationInfo->scheduleItems()),
    );
    $activeScheduleCode = $locationInfo->orderType()->getCode();

    if (!$scheduleTypes->has($activeScheduleCode)) {
        $activeScheduleCode = $scheduleTypes->keys()->first();
    }
@endphp

<h5>@lang('igniter.local::default.text_hours')</h5>
<div class="bg-white border rounded p-3 mb-4">
    <ul class="nav nav-pills justify-content-center border-bottom pb-3" role="tablist">
        @foreach ($scheduleTypes as $code => $definition)
            <li class="nav-item" role="presentation">
                <button
                    type="button"
                    role="tab"
                    id="{{$code}}-tab"
                    @class(['nav-link rounded-pill py-1', 'active' => $code === $activeScheduleCode])
                    data-bs-toggle="tab"
                    data-bs-target="#{{$code}}"
                    aria-controls="{{$code}}"
                    aria-selected="{{ $code === $activeScheduleCode ? 'true' : 'false' }}"
                >@lang(array_get($definition, 'name'))</button>
            </li>
        @endforeach
    </ul>
    <div class="tab-content">
        @foreach ($scheduleItems as $code => $schedules)
            <div
                @class(['tab-pane', 'active' => $code === $activeScheduleCode])
                id="{{$code}}"
                role="tabpanel"
                aria-labelledby="{{$code}}-tab"
            >
                <div class="list-group list-group-flush">
                    @foreach ($schedules as $day => $hours)
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between">
                                <div class="text-muted">{{ $day }}</div>
                                <div class="text-right text-nowrap text-truncate">
                                    @forelse($hours as $hour)
                                        @php($formatted = sprintf('%s-%s', $hour['open'], $hour['close']))
                                        <span title="{{ $formatted }}">{{ $formatted }}</span>
                                    @empty
                                        <span>@lang('igniter.local::default.text_closed')</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Delivery\DeliveryAvailabilityGate;
use Igniter\Local\Facades\Location;
use Igniter\Local\Models\Location as LocationModel;
use Igniter\Main\Traits\ConfigurableComponent;
use Igniter\Orange\Livewire\Concerns\SearchesNearby;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class DeliveryLocalSearch extends Component
{
    use ConfigurableComponent;
    use SearchesNearby {
        onSearchNearby as private performSearchNearby;
    }

    public bool $hideSearch = false;

    public static function componentMeta(): array
    {
        return [
            'code' => 'igniter-orange::local-search',
            'name' => 'igniter.orange::default.component_local_search_title',
            'description' => 'igniter.orange::default.component_local_search_desc',
        ];
    }

    public function defineProperties(): array
    {
        return array_merge([
            'hideSearch' => [
                'label' => 'Hide the search field and display a view menu button.',
                'type' => 'switch',
            ],
        ], $this->definePropertiesSearchNearby());
    }

    public function render(): View
    {
        return view('igniter-orange::livewire.local-search');
    }

    public function onSearchNearby()
    {
        $gate = resolve(DeliveryAvailabilityGate::class);
        $gate->assertDeliveryEnabled(Location::currentOrDefault());

        $response = $this->performSearchNearby();

        $gate->assertDeliveryEnabled(Location::currentOrDefault());
        Location::updateOrderType(LocationModel::DELIVERY);

        return $response;
    }
}

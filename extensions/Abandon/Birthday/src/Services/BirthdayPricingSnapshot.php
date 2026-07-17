<?php

declare(strict_types=1);

namespace Abandon\Birthday\Services;

final readonly class BirthdayPricingSnapshot
{
    /**
     * @param  array<int, string>  $packageIncludedItems
     * @param  array<int, array{id: int, name: string, description: ?string, price_minor: int, sort_order: int}>  $addons
     */
    public function __construct(
        public int $packageId,
        public string $packageName,
        public ?string $packageDescription,
        public array $packageIncludedItems,
        public int $packagePriceMinor,
        public array $addons,
        public int $addonsSubtotalMinor,
        public int $catalogSubtotalMinor,
        public string $currency = 'CAD',
    ) {}
}

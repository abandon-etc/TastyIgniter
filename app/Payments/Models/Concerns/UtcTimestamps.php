<?php

declare(strict_types=1);

namespace App\Payments\Models\Concerns;

use Illuminate\Support\Facades\Date;

/**
 * created_at/updated_at are written via freshTimestamp() in PHP's default
 * timezone, which TastyIgniter sets from the admin timezone Setting at
 * boot — America/Toronto in production — while the ledger's domain stamps
 * store UTC. Overriding freshTimestamp() keeps every instant in these
 * rows UTC. (Casting created_at does not work: Eloquent serializes date
 * attributes before class casts run, so a cast would relabel Toronto wall
 * time as UTC.)
 */
trait UtcTimestamps
{
    /** @return \Illuminate\Support\Carbon */
    public function freshTimestamp()
    {
        return Date::now('UTC');
    }
}

<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Abandon\Birthday\Exceptions\BirthdaySlotHoldException;
use Abandon\Birthday\Services\BirthdaySlotHoldService;
use Illuminate\Console\Command;

final class ExpireBirthdaySlotHolds extends Command
{
    protected $signature = 'birthday:expire-slot-holds {--limit=500 : Maximum holds to expire}';

    protected $description = 'Mark elapsed Birthday slot holds as expired';

    public function handle(BirthdaySlotHoldService $holds): int
    {
        try {
            $count = $holds->expireDue(limit: (int) $this->option('limit'));
        } catch (BirthdaySlotHoldException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Expired {$count} Birthday slot hold(s).");

        return self::SUCCESS;
    }
}

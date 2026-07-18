<?php

declare(strict_types=1);

namespace Tests\Feature\BirthdayBooking;

use Abandon\Birthday\Models\BirthdayBooking;
use Abandon\Birthday\Models\BirthdayBookingStatus;
use Abandon\Birthday\Models\BirthdayPackage;
use Abandon\Birthday\Models\BirthdaySlotHold;
use Abandon\Birthday\Services\BirthdaySlotHoldService;
use Carbon\CarbonImmutable;
use Igniter\Local\Models\Location;
use Igniter\User\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

final class BirthdaySlotHoldConcurrencyTest extends TestCase
{
    private Customer $customer;

    private Location $location;

    private BirthdayPackage $package;

    /** @var array<int, int> */
    private array $bookingIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        $version = (string) DB::selectOne('select version() as version')->version;
        $this->assertStringStartsWith('8.4.', $version, 'Concurrency coverage requires MySQL 8.4.');
        config()->set('birthday_booking.timezone', 'America/Toronto');

        $suffix = bin2hex(random_bytes(5));
        $this->customer = Customer::factory()->create([
            'first_name' => 'Concurrency',
            'last_name' => 'Test',
            'email' => "hold-concurrency-{$suffix}@example.invalid",
            'password' => 'test-only-password',
            'is_activated' => true,
            'status' => true,
        ]);
        $this->location = Location::factory()->create([
            'location_name' => "Hold Concurrency {$suffix}",
            'location_status' => true,
        ]);
        $this->package = BirthdayPackage::query()->create([
            'name' => "Hold Concurrency {$suffix}",
            'price_minor' => 25000,
            'currency' => 'CAD',
            'is_default' => false,
            'is_enabled' => true,
            'sort_order' => 999,
        ]);
    }

    protected function tearDown(): void
    {
        DB::table('birthday_slot_holds')->whereIn('birthday_booking_id', $this->bookingIds)->delete();
        DB::table('birthday_booking_addons')->whereIn('birthday_booking_id', $this->bookingIds)->delete();
        DB::table('birthday_bookings')->whereIn('birthday_booking_id', $this->bookingIds)->delete();
        DB::table('birthday_packages')->where('birthday_package_id', $this->package->getKey())->delete();
        DB::table('customers')->where('customer_id', $this->customer->getKey())->delete();
        DB::table('locations')->where('location_id', $this->location->getKey())->delete();

        parent::tearDown();
    }

    public function test_real_process_acquire_races_are_serialized_by_mysql_constraints_and_locks(): void
    {
        $now = CarbonImmutable::create(2026, 7, 10, 16, 0, 0, 'UTC');

        $sameSlotA = $this->booking('2026-07-12', '12-16');
        $sameSlotB = $this->booking('2026-07-12', '12-16');
        $sameSlot = $this->race([$sameSlotA, $sameSlotB], $now);
        $this->assertSame(['success', 'unavailable'], $sameSlot->pluck('status')->sort()->values()->all());
        $this->assertNoDatabaseDetails($sameSlot);
        $this->assertSame(1, $this->effectiveHolds('2026-07-12', '12-16', $now));

        $sameBooking = $this->booking('2026-07-13', '12-16');
        $idempotent = $this->race([$sameBooking, $sameBooking], $now);
        $this->assertSame(['success', 'success'], $idempotent->pluck('status')->sort()->values()->all());
        $this->assertCount(1, $idempotent->pluck('public_id')->unique());
        $this->assertCount(1, $idempotent->pluck('expires_at')->unique());
        $this->assertSame(1, BirthdaySlotHold::query()->where('birthday_booking_id', $sameBooking->getKey())->count());

        $differentA = $this->booking('2026-07-14', '12-16');
        $differentB = $this->booking('2026-07-14', '16-20');
        $differentSlots = $this->race([$differentA, $differentB], $now);
        $this->assertSame(['success', 'success'], $differentSlots->pluck('status')->sort()->values()->all());
        $this->assertSame(1, $this->effectiveHolds('2026-07-14', '12-16', $now));
        $this->assertSame(1, $this->effectiveHolds('2026-07-14', '16-20', $now));

        $expiredOwner = $this->booking('2026-07-15', '12-16');
        $expired = app(BirthdaySlotHoldService::class)->acquire($expiredOwner, $now);
        $reclaimerA = $this->booking('2026-07-15', '12-16');
        $reclaimerB = $this->booking('2026-07-15', '12-16');
        $boundary = $this->race([$reclaimerA, $reclaimerB], $now->addSeconds(900));

        $this->assertSame(['success', 'unavailable'], $boundary->pluck('status')->sort()->values()->all());
        $this->assertNoDatabaseDetails($boundary);
        $this->assertSame(1, BirthdaySlotHold::query()
            ->where('location_id', $this->location->getKey())
            ->whereDate('event_date', '2026-07-15')
            ->where('slot_code', '12-16')
            ->count());
        $winner = BirthdaySlotHold::query()
            ->where('location_id', $this->location->getKey())
            ->whereDate('event_date', '2026-07-15')
            ->where('slot_code', '12-16')
            ->firstOrFail();
        $this->assertContains($winner->birthday_booking_id, [$reclaimerA->getKey(), $reclaimerB->getKey()]);
        $this->assertNotSame($expired->public_id, $winner->public_id);
        $this->assertTrue($winner->isActiveAt($now->addSeconds(900)));
    }

    /** @param array<int, BirthdayBooking> $bookings */
    private function race(array $bookings, CarbonImmutable $now)
    {
        $directory = sys_get_temp_dir().'/birthday-hold-'.bin2hex(random_bytes(8));
        mkdir($directory, 0700, true);
        $startPath = $directory.'/start';
        $processes = [];

        try {
            foreach ($bookings as $index => $booking) {
                $readyPath = $directory.'/ready-'.$index;
                $resultPath = $directory.'/result-'.$index.'.json';
                $process = new Process([
                    PHP_BINARY,
                    '-r',
                    $this->workerScript(),
                    $readyPath,
                    $startPath,
                    $resultPath,
                    (string) $booking->getKey(),
                    $now->toIso8601String(),
                ]);
                $process->setTimeout(30);
                $process->start();
                $processes[] = $process;
            }

            $this->waitForWorkers($directory, $processes);
            touch($startPath);

            foreach ($processes as $process) {
                $this->assertSame(0, $process->wait(), $process->getErrorOutput().$process->getOutput());
            }

            return collect(array_keys($bookings))->map(function (int $index) use ($directory): array {
                return json_decode(
                    file_get_contents($directory.'/result-'.$index.'.json'),
                    true,
                    512,
                    JSON_THROW_ON_ERROR,
                );
            });
        } finally {
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $process->stop();
                }
            }

            collect(glob($directory.'/*') ?: [])->each(fn (string $path) => unlink($path));
            if (is_dir($directory)) {
                rmdir($directory);
            }
        }
    }

    private function waitForWorkers(string $directory, array $processes): void
    {
        $deadline = microtime(true) + 15;
        while (count(glob($directory.'/ready-*') ?: []) < count($processes)) {
            foreach ($processes as $process) {
                if (! $process->isRunning() && ! $process->isSuccessful()) {
                    $this->fail($process->getErrorOutput() ?: $process->getOutput());
                }
            }

            if (microtime(true) >= $deadline) {
                $this->fail('Concurrent hold workers did not reach the start barrier.');
            }

            usleep(10_000);
        }
    }

    private function workerScript(): string
    {
        return <<<'PHP'
require getcwd().'/vendor/autoload.php';
$app = require getcwd().'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

[$readyPath, $startPath, $resultPath, $bookingId, $now] = array_slice($argv, 1);
file_put_contents($readyPath, 'ready');
while (! file_exists($startPath)) {
    usleep(1_000);
}

try {
    $booking = Abandon\Birthday\Models\BirthdayBooking::query()->findOrFail((int) $bookingId);
    $hold = app(Abandon\Birthday\Services\BirthdaySlotHoldService::class)->acquire(
        $booking,
        Carbon\CarbonImmutable::parse($now),
    );
    $result = [
        'status' => 'success',
        'public_id' => $hold->public_id,
        'expires_at' => $hold->expires_at->toIso8601String(),
    ];
} catch (Abandon\Birthday\Exceptions\BirthdaySlotUnavailableException $exception) {
    $result = ['status' => 'unavailable', 'message' => $exception->getMessage()];
} catch (Abandon\Birthday\Exceptions\BirthdaySlotHoldException $exception) {
    $result = ['status' => 'domain_error', 'message' => $exception->getMessage()];
} catch (Throwable $exception) {
    $result = ['status' => 'error', 'class' => get_class($exception), 'message' => $exception->getMessage()];
}

file_put_contents($resultPath, json_encode($result, JSON_THROW_ON_ERROR));
PHP;
    }

    private function booking(string $eventDate, string $slotCode): BirthdayBooking
    {
        $startsAt = CarbonImmutable::parse("{$eventDate} ".($slotCode === '12-16' ? '16:00:00' : '20:00:00'), 'UTC');
        $booking = BirthdayBooking::query()->create([
            'public_id' => (string) Str::uuid(),
            'customer_id' => $this->customer->getKey(),
            'location_id' => $this->location->getKey(),
            'event_date' => $eventDate,
            'slot_code' => $slotCode,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addHours(4),
            'timezone' => 'America/Toronto',
            'guest_count' => 12,
            'status' => BirthdayBookingStatus::CATALOG_PRICED,
            'currency' => 'CAD',
            'package_id' => $this->package->getKey(),
            'package_name_snapshot' => 'Concurrency Package',
            'package_description_snapshot' => 'Test only',
            'package_included_items_snapshot' => ['Room'],
            'package_price_minor_snapshot' => 25000,
            'addons_subtotal_minor' => 0,
            'catalog_subtotal_minor' => 25000,
            'contact_first_name_snapshot' => 'Concurrency',
            'contact_last_name_snapshot' => 'Test',
            'contact_email_snapshot' => 'redacted@example.invalid',
            'pricing_version' => 1,
            'priced_at' => CarbonImmutable::create(2026, 7, 10, 16, 0, 0, 'UTC'),
        ]);
        $this->bookingIds[] = (int) $booking->getKey();

        return $booking;
    }

    private function effectiveHolds(string $eventDate, string $slotCode, CarbonImmutable $now): int
    {
        return BirthdaySlotHold::query()
            ->where('location_id', $this->location->getKey())
            ->whereDate('event_date', $eventDate)
            ->where('slot_code', $slotCode)
            ->where('status', 'active')
            ->where('expires_at', '>', $now->format('Y-m-d H:i:s'))
            ->count();
    }

    private function assertNoDatabaseDetails($results): void
    {
        foreach ($results as $result) {
            $message = (string) ($result['message'] ?? '');
            $this->assertStringNotContainsString('SQLSTATE', $message);
            $this->assertStringNotContainsString('birthday_slot_holds_slot_unique', $message);
            $this->assertStringNotContainsString('@example.invalid', $message);
        }
    }
}

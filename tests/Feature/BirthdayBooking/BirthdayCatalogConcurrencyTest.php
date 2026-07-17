<?php

declare(strict_types=1);

namespace Tests\Feature\BirthdayBooking;

use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

final class BirthdayCatalogConcurrencyTest extends TestCase
{
    public function test_concurrent_default_writes_have_one_winner_and_one_readable_loser(): void
    {
        $directory = sys_get_temp_dir().'/birthday-default-'.bin2hex(random_bytes(8));
        mkdir($directory, 0700, true);

        $startPath = $directory.'/start';
        $names = [
            'Concurrent package A '.bin2hex(random_bytes(4)),
            'Concurrent package B '.bin2hex(random_bytes(4)),
        ];
        $processes = [];

        try {
            foreach ($names as $index => $name) {
                $readyPath = $directory.'/ready-'.$index;
                $resultPath = $directory.'/result-'.$index.'.json';
                $process = new Process([
                    PHP_BINARY,
                    '-r',
                    $this->workerScript(),
                    $readyPath,
                    $startPath,
                    $resultPath,
                    $name,
                ]);
                $process->setTimeout(20);
                $process->start();
                $processes[] = $process;
            }

            $this->waitForWorkers($directory, $processes);
            touch($startPath);

            foreach ($processes as $process) {
                $this->assertSame(0, $process->wait(), $process->getErrorOutput());
            }

            $results = collect([0, 1])->map(function (int $index) use ($directory): array {
                return json_decode(
                    file_get_contents($directory.'/result-'.$index.'.json'),
                    true,
                    512,
                    JSON_THROW_ON_ERROR,
                );
            });

            $this->assertSame(['conflict', 'success'], $results->pluck('status')->sort()->values()->all());
            $this->assertSame(1, DB::table('birthday_packages')->whereIn('name', $names)->where('default_guard', 1)->count());

            $conflict = $results->firstWhere('status', 'conflict');
            $this->assertArrayHasKey('is_default', $conflict['errors']);
            $message = $conflict['errors']['is_default'][0];
            $this->assertStringNotContainsString('SQLSTATE', $message);
            $this->assertStringNotContainsString('birthday_packages_default_unique', $message);
        } finally {
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $process->stop();
                }
            }

            DB::table('birthday_packages')->whereIn('name', $names)->delete();
            collect(glob($directory.'/*') ?: [])->each(fn (string $path) => unlink($path));
            if (is_dir($directory)) {
                rmdir($directory);
            }
        }
    }

    private function waitForWorkers(string $directory, array $processes): void
    {
        $deadline = microtime(true) + 10;
        while (count(glob($directory.'/ready-*') ?: []) < count($processes)) {
            foreach ($processes as $process) {
                if (! $process->isRunning() && ! $process->isSuccessful()) {
                    $this->fail($process->getErrorOutput() ?: $process->getOutput());
                }
            }

            if (microtime(true) >= $deadline) {
                $this->fail('Concurrent default workers did not reach the start barrier.');
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

[$readyPath, $startPath, $resultPath, $name] = array_slice($argv, 1);
file_put_contents($readyPath, 'ready');
while (!file_exists($startPath)) {
    usleep(1_000);
}

try {
    app(Abandon\Birthday\Services\BirthdayPackageService::class)->runAtomicSave(function () use ($name): void {
        Illuminate\Support\Facades\DB::table('birthday_packages')->insert([
            'name' => $name,
            'price_minor' => 100,
            'currency' => 'CAD',
            'is_default' => true,
            'is_enabled' => true,
            'sort_order' => 0,
            'default_guard' => 1,
        ]);
    });
    $result = ['status' => 'success'];
} catch (Illuminate\Validation\ValidationException $exception) {
    $result = ['status' => 'conflict', 'errors' => $exception->errors()];
} catch (Throwable $exception) {
    $result = ['status' => 'error', 'class' => get_class($exception)];
}

file_put_contents($resultPath, json_encode($result, JSON_THROW_ON_ERROR));
PHP;
    }
}

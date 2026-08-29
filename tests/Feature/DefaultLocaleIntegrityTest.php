<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\DefaultLocaleIntegrity;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Pins the invariant that the site's default language is French.
 *
 * Note what this can and cannot do. CI migrates a throwaway database and has no
 * production rows, so `Language::getDefault()` is null here and asserting
 * against live data is structurally impossible in CI. What CI *can* pin is the
 * expectation itself and the guard's behaviour; the live comparison is made at
 * boot by the guard, against this constant.
 *
 * The test on the constant is the point of the exercise: someone changing the
 * default language has to change it, which fails this test and puts the reason
 * in front of them.
 */
final class DefaultLocaleIntegrityTest extends TestCase
{
    public function test_the_expected_default_locale_is_french(): void
    {
        $this->assertSame(
            'fr_CA',
            DefaultLocaleIntegrity::EXPECTED,
            'The default language is not a preference, it is what gives existing content its meaning. '
            .'Model columns such as ti_menus.menu_name carry no language tag - their language is whichever '
            .'default is in force when they are read. Changing this constant without migrating that content '
            .'reinterprets every menu name and description the owner typed in French as another language, '
            .'silently and without error. If that is genuinely intended, migrate the stored content first.',
        );
    }

    public function test_the_expected_default_is_reported_as_correct_and_logs_nothing(): void
    {
        Log::spy();

        $this->assertTrue(DefaultLocaleIntegrity::report('fr_CA'));

        Log::shouldNotHaveReceived('warning');
    }

    public function test_a_different_default_is_reported_and_logged(): void
    {
        Log::spy();

        $this->assertFalse(DefaultLocaleIntegrity::report('en'));

        Log::shouldHaveReceived('warning')->once()->withArgs(
            fn(string $message): bool => str_contains($message, '[en]')
                && str_contains($message, '[fr_CA]'),
        );
    }

    public function test_an_unreadable_default_is_reported_and_logged(): void
    {
        Log::spy();

        $this->assertFalse(DefaultLocaleIntegrity::report(null));

        Log::shouldHaveReceived('warning')->once()->withArgs(
            fn(string $message): bool => str_contains($message, 'unreadable'),
        );
    }

    public function test_the_guard_is_wired_into_the_boot_sequence(): void
    {
        $source = file_get_contents(base_path('app/Providers/AppServiceProvider.php'));

        $this->assertStringContainsString('DefaultLocaleIntegrity::report', $source,
            'The guard must run at boot; a class nobody calls reports nothing.');
    }
}

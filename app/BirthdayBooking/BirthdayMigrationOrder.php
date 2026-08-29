<?php

namespace App\BirthdayBooking;

use Igniter\Flame\Support\Facades\Igniter;
use Illuminate\Support\Facades\Log;
use ReflectionException;
use ReflectionProperty;

/**
 * Moves the abandon.birthday migration group to the end of the extension
 * migration map.
 *
 * The installed core registers local extensions before every vendor
 * extension and runs migration groups in registration order, with no
 * dependency ordering of any kind (ExtensionManager::loadExtensions,
 * UpdateManager::migrate -> Migrator::runGroup). On a fresh database the
 * abandon.birthday group would therefore migrate before igniter.user,
 * igniter.local and igniter.reservation — whose tables its own migrations
 * reference — and fail. Reproduced in CI on 2026-08-28 (draft PR #122,
 * run 3).
 *
 * The map lives in a protected property with an append-only public API, so
 * the reorder uses reflection. The group KEY is unchanged: the migrations
 * ledger identifies rows by group name, and already-initialized databases
 * keep their history. A vendor upgrade that renames the property is caught
 * by the unit test that pins this contract; at runtime the failure is
 * logged and boot continues, because a broken reorder only matters when
 * migrations run, and the guarded migration then fails loudly on its own.
 */
class BirthdayMigrationOrder
{
    public const GROUP = 'abandon.birthday';

    public static function apply(): void
    {
        try {
            $igniter = Igniter::getFacadeRoot();

            $property = new ReflectionProperty($igniter, 'migrationPaths');
            /** @var array<string, string> $paths */
            $paths = $property->getValue($igniter);

            if (!isset($paths[self::GROUP]) || array_key_last($paths) === self::GROUP) {
                return;
            }

            $path = $paths[self::GROUP];
            unset($paths[self::GROUP]);
            $paths[self::GROUP] = $path;

            $property->setValue($igniter, $paths);
        } catch (ReflectionException $e) {
            Log::error('BirthdayMigrationOrder could not reorder the migration map: '.$e->getMessage());
        }
    }
}

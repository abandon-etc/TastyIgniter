<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Guards the one setting that silently reinterprets existing menu content.
 *
 * The translate extension, and TastyIgniter's own locale resolution, treat a
 * model's ordinary columns as holding the *default* language. `ti_menus.menu_name`
 * is not tagged with a language anywhere; its language is whichever
 * `Language::getDefault()->code` happens to be at read time. Translations for
 * every other language live in a separate table.
 *
 * So changing the default language does not migrate anything and does not fail.
 * It reinterprets. Every menu name and description the owner typed in French
 * would, from that moment, be served as the new default language, and French
 * customers would get it as a fallback for a language it is not. Nothing throws,
 * nothing is logged by the framework, and the content still renders - it is
 * simply labelled wrong everywhere.
 *
 * That failure shape has appeared repeatedly in this project: the timezone
 * failing open to UTC, and `supported_languages` not refreshing after a direct
 * database write. Each was a change that succeeded, reported nothing, and was
 * wrong. This makes the assumption executable instead of documentary.
 *
 * The expectation lives in a class constant rather than configuration on
 * purpose: changing it then requires a code change, which appears in a diff and
 * trips the test that pins it, so whoever intends to change the default language
 * is told what it costs before they can do it.
 */
final class DefaultLocaleIntegrity
{
    /**
     * The language whose content is stored in the models' own columns.
     *
     * Read the class docblock before changing this. Changing it without
     * migrating the existing column content reinterprets every stored menu name
     * and description as a different language.
     */
    public const EXPECTED = 'fr_CA';

    /**
     * @param string|null $storedDefault the code of the default Language record,
     *                                   or null when there is no readable
     *                                   default - a fresh or absent database
     *
     * @return bool whether the invariant holds (null holds vacuously)
     */
    public static function report(?string $storedDefault): bool
    {
        // No default at all is a database with no content to misinterpret:
        // CI's throwaway schema, a fresh install, or a boot before any
        // database exists (composer's config:clear boots the app that way).
        // There is nothing to guard yet, so this passes silently - warning
        // here would fail every fresh environment for its freshness. A
        // *different* default is the dangerous case: content exists and is
        // now labelled as another language.
        if ($storedDefault === null || $storedDefault === self::EXPECTED) {
            return true;
        }

        Log::warning(sprintf(
            'Default language is [%s], expected [%s]. Model columns such as '
            .'menu_name carry no language tag: their language is whichever default '
            .'is in force at read time. While this mismatch persists, content '
            .'entered as [%s] is served as [%s] and used as the fallback for every '
            .'untranslated language. Nothing else will report this.',
            $storedDefault,
            self::EXPECTED,
            self::EXPECTED,
            $storedDefault,
        ));

        return false;
    }
}

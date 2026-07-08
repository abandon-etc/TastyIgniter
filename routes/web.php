<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;

Route::get('/language/{locale}', function(string $locale) {
    abort_unless(in_array($locale, ['fr_CA', 'en_CA'], true), 404);

    $localization = app('translator.localization');
    $localization->setSessionLocale($locale);
    $localization->setLocale($locale);

    $previousUrl = url()->previous();
    $baseUrl = url('/');
    $previousParts = parse_url($previousUrl);
    $baseParts = parse_url($baseUrl);

    $isSameSite = ($previousParts['scheme'] ?? null) === ($baseParts['scheme'] ?? null)
        && ($previousParts['host'] ?? null) === ($baseParts['host'] ?? null)
        && ($previousParts['port'] ?? null) === ($baseParts['port'] ?? null);

    $targetUrl = $isSameSite ? $previousUrl : $baseUrl;

    return redirect()->to($targetUrl);
})->name('language.switch');

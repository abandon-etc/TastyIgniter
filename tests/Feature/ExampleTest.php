<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The stock scaffold expected `/` to answer 200 directly, but this
     * site's root redirects into the single-location storefront — that
     * redirect chain ending on a served page is the actual contract, and
     * following it keeps this as a real storefront smoke test. (Fix, not
     * quarantine: first exposed when the suite first ran in CI,
     * 2026-08-29.)
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        $response = $this->followingRedirects()->get('/');

        $response->assertStatus(200);
    }
}

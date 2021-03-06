<?php

namespace Tests\Browser\Tests\Admin;

use Naraki\Sentry\Models\Person;
use Naraki\Sentry\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\CreatesDatabaseResources;
use Tests\DuskTestCase;

class BasicTest extends DuskTestCase
{
    use DatabaseMigrations, CreatesDatabaseResources;

    /**
     * A basic browser test example.
     *
     * @return void
     * @throws \Throwable
     */
    public function test_see_login()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(route_i18n('admin.login'))
                ->assertPresent('input[type="email"]')
                ->assertPresent('input[type="password"]');

        });
    }

    public function test_login()
    {
        $u = $this->createManualUser();
        $this->browse(function ($browser) use ($u) {
            $browser->visit(route_i18n('admin.login'))
                ->type('email', $u->email)
                ->type('password', 'secret')
                ->press('Log In')
                ->waitUntilMissing('input[type="password"]')
                ->assertPathIs('/admin/dashboard');
        });
    }
}

<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ec-doris/laravel-cas
 */

declare(strict_types=1);

namespace EcDoris\LaravelCas\Tests\Unit;

use EcDoris\LaravelCas\Middleware\CasAuthenticator;
use EcDoris\LaravelCas\Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class LoginControllerTest extends TestCase
{
    public function test_it_redirects_to_the_upstream_cas_login_endpoint()
    {
        $response = $this->get('/login');

        $response->assertRedirect('https://webgate.ec.europa.eu/cas/login?service=http%3A%2F%2Flocalhost%2Fcas%2Fcallback');
    }

    public function test_it_does_not_loop_back_to_login_when_cas_authenticator_is_added_to_the_web_group()
    {
        $this->app['router']->pushMiddlewareToGroup('web', CasAuthenticator::class);

        $response = $this->get('/login');

        $response->assertRedirect('https://webgate.ec.europa.eu/cas/login?service=http%3A%2F%2Flocalhost%2Fcas%2Fcallback');
        $this->assertNotSame(route('laravel-cas-login'), $response->headers->get('Location'));
    }
}

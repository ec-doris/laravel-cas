<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ec-doris/laravel-cas
 */

declare(strict_types=1);

namespace EcDoris\LaravelCas\Tests\Unit;

use EcDoris\LaravelCas\Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class LogoutControllerTest extends TestCase
{
    private $response;

    private $uri = 'logout';

    protected function setUp(): void
    {
        parent::setUp();

        $this->response = $this->get($this->uri);
    }

    public function testIfRedirectUri()
    {
        $this->response->assertRedirect('https://webgate.ec.europa.eu/cas/logout?service=http%3A%2F%2Flocalhost');
    }

    public function testMasqueradeModeSkipsCasLogout()
    {
        config(['app.env' => 'local']);
        config(['laravel-cas.masquerade' => 'test@example.com']);
        config(['laravel-cas.redirect_logout_url' => 'http://localhost/home']);

        $response = $this->get($this->uri);

        // Should redirect directly to the configured logout URL without CAS
        $response->assertRedirect('http://localhost/home');
    }

    public function testDemoModeSkipsCasLogout()
    {
        config(['app.env' => 'local']);
        config(['laravel-cas.demo_mode' => true]);
        config(['laravel-cas.redirect_logout_url' => 'http://localhost/goodbye']);

        $response = $this->get($this->uri);

        // Should redirect directly to the configured logout URL without CAS
        $response->assertRedirect('http://localhost/goodbye');
    }

    public function testMasqueradeModeUsesDefaultRedirect()
    {
        config(['app.env' => 'local']);
        config(['laravel-cas.masquerade' => 'test@example.com']);
        config(['laravel-cas.redirect_logout_url' => null]);

        $response = $this->get($this->uri);

        // Should use default '/' when no redirect_logout_url is configured
        $response->assertRedirect('/');
    }

    public function testDemoModeUsesDefaultRedirect()
    {
        config(['app.env' => 'local']);
        config(['laravel-cas.demo_mode' => true]);
        config(['laravel-cas.redirect_logout_url' => null]);

        $response = $this->get($this->uri);

        // Should use default '/' when no redirect_logout_url is configured
        $response->assertRedirect('/');
    }

    public function testNormalModePerformsCasLogout()
    {
        config(['app.env' => 'production']);
        config(['laravel-cas.masquerade' => null]);
        config(['laravel-cas.demo_mode' => false]);

        $response = $this->get($this->uri);

        // Should redirect to CAS logout URL in normal mode
        $response->assertRedirect('https://webgate.ec.europa.eu/cas/logout?service=http%3A%2F%2Flocalhost');
    }
}

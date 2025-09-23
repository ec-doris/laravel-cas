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
class LoginControllerTest extends TestCase
{
    private $response;

    private $uri = 'login';

    protected function setUp(): void
    {
        parent::setUp();
        $this->response = $this->get($this->uri);
    }

    public function testIfRedirectUri()
    {
        $this->response->assertRedirect('https://webgate.ec.europa.eu/cas/login?service=http%3A%2F%2Flocalhost%2Flogin');
    }
}

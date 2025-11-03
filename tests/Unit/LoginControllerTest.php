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
        $this->response->assertRedirect('http://localhost/login?service=http://localhost/cas/callback');
    }
}

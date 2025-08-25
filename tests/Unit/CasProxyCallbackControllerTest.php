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
class CasProxyCallbackControllerTest extends TestCase
{
    private $response;

    protected function setUp(): void
    {
        parent::setUp();

        $this->response = $this->get(route('laravel-cas-proxy-callback'));
    }

    public function testIfNotFalse()
    {
        self::assertNotFalse($this->response);
    }

    public function testIfXml()
    {
        self::assertEquals('', $this->response->getContent());
    }
}

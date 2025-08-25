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
class HomePageControllerTest extends TestCase
{
    public function testHomePageController()
    {
        $response = $this->get('/homepage');

        $shouldReturnTxt = '<p>You have been redirected here by default.
You are most probably using the default LARAVEL CAS configuration.</p>
<p>The default LARAVEL CAS configuration should be installed in
<code>config/laravel-cas.php</code></p>
<p>Please update your .env file configuration and add <code>CAS_REDIRECT_LOGIN_ROUTE</code>
with an existing route of your app.</p>';
        self::assertEquals($shouldReturnTxt, $response->getContent());
    }
}

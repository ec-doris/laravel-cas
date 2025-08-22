<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/brownrl/laravel-cas
 */

declare(strict_types=1);

namespace EcPhp\LaravelCas\Controllers;

use Illuminate\Http\Response;

class HomepageController extends Controller
{
    public function __invoke(): Response
    {
        $body = <<< 'EOF'
            <p>You have been redirected here by default.
            You are most probably using the default LARAVEL CAS configuration.</p>
            <p>The default LARAVEL CAS configuration should be installed in
            <code>config/laravel-cas.php</code></p>
            <p>Please update your .env file configuration and add <code>CAS_REDIRECT_LOGIN_ROUTE</code>
            with an existing route of your app.</p>
            EOF;

        return new Response($body);
    }
}

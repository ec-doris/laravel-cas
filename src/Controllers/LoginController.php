<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ec-doris/laravel-cas
 */

declare(strict_types=1);

namespace EcDoris\LaravelCas\Controllers;

use EcPhp\CasLib\Contract\CasInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoginController extends Controller
{
    public function __invoke(
        Request $request,
        CasInterface $cas,
        ServerRequestInterface $serverRequest,
    ): Redirector|RedirectResponse|ResponseInterface {

        if (strtolower((string) config('app.env')) === 'production' && config('laravel-cas.demo_mode')) {
            throw new \Exception('Demo mode cannot be used in a production environment.');
        }

        if (strtolower((string) config('app.env')) !== 'production' && config('laravel-cas.demo_mode')) {
            $returnUrl = route('laravel-cas-callback', [], true);
            $demoLoginUrl = config('laravel-cas.demo_login_url', 'https://demo-eulogin.cnect.eu');
            
            return redirect($demoLoginUrl . '?returnto=' . urlencode($returnUrl));
        }

        if (strtolower((string) config('app.env')) === 'production' && ! is_null(config('laravel-cas.masquerade'))) {
            throw new \Exception('Masquerade cannot be used in a production environment.');
        }

        if (strtolower((string) config('app.env')) !== 'production' && ! is_null(config('laravel-cas.masquerade'))) {
            auth('laravel-cas')->masquerade();

            $redirectRoute = config('laravel-cas.redirect_login_route', 'dashboard');
            
            try {
                return redirect(route($redirectRoute));
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                return redirect('/');
            }
        }

        $casUrl = config('laravel-cas.base_url');
        $serviceUrl = route('laravel-cas-callback');

        return redirect(sprintf('%s/login?service=%s', $casUrl, $serviceUrl));
    }
}

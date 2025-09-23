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

        if (strtolower((string) config('app.env')) !== 'production' && ! is_null(config('laravel-cas.masquerade'))) {
            auth('laravel-cas')->masquerade();

            return redirect(route(config('laravel-cas.redirect_login_route')));
        }

        $parameters = $request->query->all() + [
            'renew' => null !== auth()->guard()->user(),
        ];

        return $cas->login($serverRequest->withQueryParams($parameters));
    }
}

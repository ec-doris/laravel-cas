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

class LogoutController extends Controller
{
    public function __invoke(
        Request $request,
        CasInterface $cas,
        ServerRequestInterface $serverRequest
    ): Redirector|RedirectResponse|ResponseInterface {
        // In masquerade or demo mode, only clear the local session without CAS logout
        $isMasqueradeMode = strtolower((string) config('app.env')) !== 'production' && ! is_null(config('laravel-cas.masquerade'));
        $isDemoMode = strtolower((string) config('app.env')) !== 'production' && config('laravel-cas.demo_mode');
        
        if ($isMasqueradeMode || $isDemoMode) {
            if (auth()->check()) {
                auth()->logout();
            }
            
            $redirectUrl = config('laravel-cas.redirect_logout_url') ?: '/';
            return redirect($redirectUrl);
        }

        // Normal mode: perform CAS logout
        $response = $cas
            ->logout(
                $serverRequest->withQueryParams(
                    $request->query->all()
                )
            );

        if (auth()->check()) {
            auth()->logout();

            $redirectUrl = config('laravel-cas.redirect_logout_url') ?: '/';
            return redirect($redirectUrl);
        }

        return $response;
    }
}

<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ec-doris/laravel-cas
 */

declare(strict_types=1);

namespace EcDoris\LaravelCas\Middleware;

use Closure;
use EcPhp\CasLib\Contract\CasInterface;
use EcPhp\CasLib\Contract\Response\Type\ServiceValidate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

use function is_array;
use function is_string;
use function json_decode;
use function strtolower;
use function str_starts_with;
use function substr;
use function urldecode;

class CasAuthenticator
{
    public function __construct(
        private CasInterface $cas,
        private ServerRequestInterface $serverRequest
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $guard = auth('laravel-cas');

        if ($guard->check()) {
            return $next($request);
        }

        if (strtolower((string) config('app.env')) === 'production' && config('laravel-cas.demo_mode')) {
            throw new RuntimeException('Demo mode cannot be used in a production environment.');
        }

        if (strtolower((string) config('app.env')) !== 'production' && config('laravel-cas.demo_mode')) {
            $ticket = $request->query('ticket');

            if (is_string($ticket) && str_starts_with($ticket, 'DEMO_')) {
                return $this->handleDemoTicket($ticket);
            }
        }

        if (!$this->cas->supportAuthentication($this->serverRequest)) {
            return $this->redirectToLogin();
        }

        try {
            /** @var ServiceValidate $response */
            $response = $this->cas->requestTicketValidation($this->serverRequest);

            if (!$guard->attempt($response->getCredentials())) {
                throw new RuntimeException('Unable to authenticate the CAS user.');
            }

            return $this->redirectAfterLogin();
        } catch (\Exception) {
            return new Response(
                '<h1>Authentication Error</h1><p>Sorry, we were unable to authenticate you at this time. Please try again later.</p>',
                500
            );
        }
    }

    private function handleDemoTicket(string $ticket): RedirectResponse
    {
        if (strtolower((string) config('app.env')) === 'production') {
            throw new RuntimeException('Demo mode cannot be used in a production environment.');
        }

        $jsonPayload = urldecode(substr($ticket, 5));
        $data = json_decode($jsonPayload, true);

        if (!is_array($data) || !isset($data['email']) || !is_string($data['email']) || $data['email'] === '') {
            throw new RuntimeException('Invalid demo ticket: email is required.');
        }

        $authenticated = auth('laravel-cas')->attempt([
            'user' => $data['email'],
            'attributes' => [
                'email' => $data['email'],
                'firstName' => $data['firstName'] ?? 'Demo',
                'lastName' => $data['lastName'] ?? 'User',
                'departmentNumber' => $data['departmentNumber'] ?? null,
            ],
        ]);

        if ($authenticated === null) {
            throw new RuntimeException('Unable to authenticate the demo user.');
        }

        return $this->redirectAfterLogin();
    }

    private function redirectAfterLogin(): RedirectResponse
    {
        $redirectRoute = config('laravel-cas.redirect_login_route', 'dashboard');

        try {
            return redirect()->intended(route($redirectRoute));
        } catch (RouteNotFoundException) {
            return redirect()->intended('/');
        }
    }

    private function redirectToLogin(): RedirectResponse
    {
        try {
            return redirect()->guest(route('laravel-cas-login'));
        } catch (RouteNotFoundException) {
            return redirect()->guest('/login');
        }
    }
}

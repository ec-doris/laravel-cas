<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ec-doris/laravel-cas
 */

declare(strict_types=1);

namespace EcDoris\LaravelCas\Middleware;

use App\Models\User;
use Closure;
use EcPhp\CasLib\Contract\CasInterface;
use EcPhp\CasLib\Contract\Response\Type\ServiceValidate;
use Illuminate\Http\Request;
use Psr\Http\Message\ServerRequestInterface;

use function json_decode;
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
        if (strtolower((string) config('app.env')) === 'production' && config('laravel-cas.demo_mode')) {
            throw new \Exception('Demo mode cannot be used in a production environment.');
        }

        if (strtolower((string) config('app.env')) !== 'production' && config('laravel-cas.demo_mode')) {
            $ticket = $request->query('ticket');
            
            if ($ticket && str_starts_with($ticket, 'DEMO_')) {
                return $this->handleDemoTicket($ticket);
            }
        }

        if (!$this->cas->supportAuthentication($this->serverRequest)) {
            return $next($request);
        }

        /** @var ServiceValidate $response */
        $response = $this->cas->requestTicketValidation($this->serverRequest);

        auth('laravel-cas')->attempt($response->getCredentials());

        // Try to redirect to configured route, fallback to homepage or dashboard
        $redirectRoute = config('laravel-cas.redirect_login_route', 'laravel-cas-homepage');
        
        try {
            return redirect(route($redirectRoute));
        } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
            // Fallback to URL if route doesn't exist
            return redirect('/homepage');
        }
    }

    private function handleDemoTicket(string $ticket): mixed
    {
        if (strtolower((string) config('app.env')) === 'production') {
            throw new \Exception('Demo mode cannot be used in a production environment.');
        }

        $jsonPayload = substr($ticket, 5);
        $jsonPayload = urldecode($jsonPayload);
        
        $data = json_decode($jsonPayload, true);
        
        if (!$data || !isset($data['email'])) {
            throw new \Exception('Invalid demo ticket: email is required.');
        }

        $email = $data['email'];
        $firstName = $data['firstName'] ?? 'Demo';
        $lastName = $data['lastName'] ?? 'User';
        $departmentNumber = $data['departmentNumber'] ?? null;
        
        $name = trim($firstName . ' ' . $lastName);
        $name = ucwords(strtolower($name));

        $user = User::where('email', $email)->first();

        if ($user) {
            auth('laravel-cas')->setUser($user);
        } else {
            $attributes = [
                'email' => $email,
                'name' => $name,
                'password' => 'xxx-xxx-xxx-xxx',
            ];

            if ($departmentNumber) {
                $userModel = new User();
                if (in_array('departmentNumber', $userModel->getFillable()) || $userModel->getGuarded() === ['*'] || $userModel->getGuarded() === []) {
                    $attributes['departmentNumber'] = $departmentNumber;
                }
                if (in_array('organisation', $userModel->getFillable()) || $userModel->getGuarded() === ['*'] || $userModel->getGuarded() === []) {
                    $attributes['organisation'] = $departmentNumber;
                }
            }

            $user = User::create($attributes);
            auth('laravel-cas')->setUser($user);
        }

        $redirectRoute = config('laravel-cas.redirect_login_route', 'laravel-cas-homepage');
        
        try {
            return redirect(route($redirectRoute));
        } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
            return redirect('/homepage');
        }
    }
}

<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ec-doris/laravel-cas
 */

declare(strict_types=1);

namespace EcDoris\LaravelCas\Auth;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard as AuthGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;

use function sprintf;

class CasGuard implements AuthGuard
{
    private bool $loggedOut = false;

    private string $name = 'laravel-cas';

    private ?Authenticatable $user = null;

    public function __construct(
        private ?UserProvider $provider,
        private Request $request,
        private Session $session
    ) {}

    /**
     * Handle masquerading for development environments
     */
    public function masquerade()
    {
        if (strtolower((string) config('app.env')) === 'production' && config('laravel-cas.masquerade')) {
            throw new \Exception('Masquerade cannot be used in a production environment.');
        }

        $password = 'xxx-xxx-xxx-xxx';
        $name = 'Cas Masquerade';
        $email = config('laravel-cas.masquerade');
        
        // Normalize email to lowercase for case-insensitive matching
        $email = strtolower($email);
        
        $laravelUser = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if ($laravelUser) {
            $this->setUser($laravelUser);
            return $laravelUser;
        }

        $attributes = [
            'email' => $email,
            'name' => $name,
            'password' => $password,
        ];

        $laravelUser = User::create($attributes);
        $this->setUser($laravelUser);

        return $laravelUser;
    }

    public function attempt(array $credentials): ?Authenticatable
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        if ($user === null) {
            return null;
        }

        $this->setUser($user);

        return $user;
    }

    public function check()
    {
        return $this->user() !== null;
    }

    public function getJsonParams()
    {
        return null;
    }

    public function getName()
    {
        return sprintf('login_%s_%s', $this->name, sha1(self::class));
    }

    public function guest()
    {
        return ! $this->check();
    }

    public function hasUser()
    {
        return ($this->user() !== null) ? true : false;
    }

    public function id()
    {
        if ($this->loggedOut || ! $this->hasUser()) {
            return null;
        }

        $user = $this->user();
        return $user ? $user->getAuthIdentifier() : null;
    }

    public function logout()
    {
        $this->user = null;
        $this->loggedOut = true;
        $this->session->remove($this->getName());
        $this->session->migrate(true);
    }

    public function setUser(Authenticatable $user)
    {
        $this->user = $user;
        $this->loggedOut = false;
        $this->session->put($this->getName(), $user);
        $this->session->migrate(true);
    }

    public function user()
    {
        if ($this->loggedOut) {
            return null;
        }

        return $this->provider->retrieveCasUser();
    }

    public function validate(array $credentials = [])
    {
        if ($credentials === []) {
            return false;
        }

        return true;
    }
}

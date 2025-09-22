<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ec-doris/laravel-cas
 */

declare(strict_types=1);

namespace EcDoris\LaravelCas\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard as AuthGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function masquerade()
    {
        if (strtolower((string) config('app.env')) === 'production' && config('cas.cas_masquerade')) {
            throw new \Exception('Masquerade cannot be used in a production environment.');
        }

        

        $attributes = [
            'email' => config('cas.cas_masquerade'),
            'name' => 'Cas Masquerade',
            'password' => 'xxx-xxx-xxx-xxx'
        ];

        $laravelUser = \App\Models\User::firstOrCreate($attributes);

        $this->setUser($laravelUser);

        return $laravelUser;
    }

    public function attempt(array $credentials): ?Authenticatable
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        if (null === $user) {
            return null;
        }

        $this->setUser($user);

        return $user;
    }

    public function check()
    {
        return null !== $this->user();
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
        return !$this->check();
    }

    public function hasUser()
    {
        return (null !== $this->user()) ? true : false;
    }

    public function id()
    {
        if ($this->loggedOut || ! $this->hasUser()) {
            return null;
        }

        return $this->user->user ?? null;
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
        if ([] === $credentials) {
            return false;
        }

        return true;
    }
}

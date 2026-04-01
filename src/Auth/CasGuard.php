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
use RuntimeException;

use function is_string;
use function sha1;
use function sprintf;
use function strtolower;
use function trim;

class CasGuard implements AuthGuard
{
    private bool $loggedOut = false;

    private string $name = 'laravel-cas';

    private ?Authenticatable $user = null;

    public function __construct(
        private UserProvider $provider,
        private Session $session
    ) {}

    /**
     * Handle masquerading for development environments.
     */
    public function masquerade(): Authenticatable
    {
        if (strtolower((string) config('app.env')) === 'production' && config('laravel-cas.masquerade')) {
            throw new RuntimeException('Masquerade cannot be used in a production environment.');
        }

        $email = config('laravel-cas.masquerade');

        if (!is_string($email) || trim($email) === '') {
            throw new RuntimeException('Masquerade requires a valid email address.');
        }

        $user = $this->attempt([
            'user' => $email,
            'attributes' => [
                'email' => $email,
                'firstName' => 'Cas',
                'lastName' => 'Masquerade',
            ],
        ]);

        if (!$user instanceof Authenticatable) {
            throw new RuntimeException('Unable to masquerade as the configured user.');
        }

        return $user;
    }

    public function attempt(array $credentials): ?Authenticatable
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        if (!$user instanceof Authenticatable) {
            return null;
        }

        $this->setUser($user);

        return $user;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function getJsonParams()
    {
        return null;
    }

    public function getName(): string
    {
        return sprintf('login_%s_%s', $this->name, sha1(self::class));
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function hasUser(): bool
    {
        return $this->user() !== null;
    }

    public function id()
    {
        $user = $this->user();

        return $user ? $user->getAuthIdentifier() : null;
    }

    public function logout(): void
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
        $this->session->put($this->getName(), $user->getAuthIdentifier());
        $this->session->migrate(true);

        return $this;
    }

    public function user()
    {
        if ($this->loggedOut) {
            return null;
        }

        if ($this->user instanceof Authenticatable) {
            return $this->user;
        }

        $identifier = $this->session->get($this->getName());

        if ($identifier === null) {
            return null;
        }

        $user = $this->provider->retrieveById($identifier);
        $this->user = $user instanceof Authenticatable ? $user : null;

        return $this->user;
    }

    public function validate(array $credentials = []): bool
    {
        return $credentials !== [];
    }
}

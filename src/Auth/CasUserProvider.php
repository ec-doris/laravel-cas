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
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Session\Session;

use function array_key_exists;

class CasUserProvider implements UserProvider
{
    private string $guard_name = 'laravel-cas';

    private Authenticatable $model;

    public function __construct(
        private Session $session
    ) {}

    public function getModel(): ?Authenticatable
    {
        return $this->model;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false) {}

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {

        if ($credentials === []) {
            return null;
        }

        if (array_key_exists('user', $credentials) === false) {
            return null;
        }

        // Extract email/user info from CAS credentials
        $email = $credentials['attributes']['email'] ?? null;

        if (! $email) {
            return null;
        }
        
        $password = 'xxx-xxx-xxx-xxx';
        $name = ($credentials['attributes']['firstName'].' '.$credentials['attributes']['lastName']) ?? '';
        $name = ucwords(strtolower($name));
        $departmentNumber = $credentials['attributes']['departmentNumber'] ?? null;

        $attributes = [
            'email' => $email,
            'name' => $name,
            'password' => $password,
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

        // Find existing user or create new one
        $laravelUser = User::where('email', $email)->first();

        if ($laravelUser) {
            // User exists - use it
            $this->model = $laravelUser;
            return $this->model;
        }

        // Create new Laravel User
        $laravelUser = User::create($attributes);
        $this->model = $laravelUser;
        return $this->model;
    }

    public function retrieveById($identifier)
    {
        return null;
    }

    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    public function retrieveCasUser(): ?Authenticatable
    {
        // Replicate CasGuard::getName() logic to avoid circular dependency
        $sessionKey = sprintf('login_%s_%s', $this->guard_name, sha1(\EcDoris\LaravelCas\Auth\CasGuard::class));

        return $this->session->get($sessionKey);
    }

    public function updateRememberToken(Authenticatable $user, $token) {}

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return true;
    }
}

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
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

use function array_filter;
use function class_exists;
use function implode;
use function is_string;
use function sprintf;
use function strtolower;
use function trim;
use function ucwords;

class CasUserProvider implements UserProvider
{
    public function __construct(
        private string $modelClass
    ) {}

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void {}

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if ($credentials === []) {
            return null;
        }

        $casAttributes = $credentials['attributes'] ?? null;

        if (!is_array($casAttributes)) {
            return null;
        }

        $email = $casAttributes['email'] ?? null;

        if (!is_string($email) || $email === '') {
            return null;
        }

        $email = strtolower($email);
        $existingUser = $this
            ->newModelQuery()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if ($existingUser instanceof Authenticatable) {
            return $existingUser;
        }

        $user = $this->newModelInstance();
        $user->fill($this->buildAttributes($casAttributes, $email, $user));
        $user->save();

        return $user;
    }

    public function retrieveById($identifier): ?Authenticatable
    {
        $user = $this->newModelQuery()->find($identifier);

        return $user instanceof Authenticatable ? $user : null;
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        $user = $this->retrieveById($identifier);

        if (!$user instanceof Authenticatable) {
            return null;
        }

        return $user->getRememberToken() === $token ? $user : null;
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        if (!$user instanceof Model) {
            return;
        }

        $user->setRememberToken($token);
        $user->save();
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        return true;
    }

    private function buildAttributes(array $casAttributes, string $email, Model $user): array
    {
        $attributes = [
            'email' => $email,
            'name' => $this->formatName(
                $casAttributes['firstName'] ?? null,
                $casAttributes['lastName'] ?? null,
                $email
            ),
            'password' => 'xxx-xxx-xxx-xxx',
        ];

        $departmentNumber = $casAttributes['departmentNumber'] ?? null;

        if (is_string($departmentNumber) && $departmentNumber !== '') {
            foreach (['departmentNumber', 'department_number', 'organisation'] as $column) {
                if ($user->isFillable($column)) {
                    $attributes[$column] = $departmentNumber;
                }
            }
        }

        return $attributes;
    }

    private function formatName(mixed $firstName, mixed $lastName, string $fallback): string
    {
        $fullName = trim(implode(' ', array_filter([$firstName, $lastName], 'is_string')));

        if ($fullName === '') {
            return $fallback;
        }

        return ucwords(strtolower($fullName));
    }

    private function newModelInstance(): Model
    {
        if (!class_exists($this->modelClass)) {
            throw new InvalidArgumentException(
                sprintf('The configured CAS user model [%s] could not be found.', $this->modelClass)
            );
        }

        $model = new $this->modelClass();

        if (!$model instanceof Model || !$model instanceof Authenticatable) {
            throw new InvalidArgumentException(
                sprintf('The configured CAS user model [%s] must extend Eloquent Model and implement Authenticatable.', $this->modelClass)
            );
        }

        return $model;
    }

    private function newModelQuery()
    {
        return $this->newModelInstance()->newQuery();
    }
}

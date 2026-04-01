<?php

declare(strict_types=1);

namespace EcDoris\LaravelCas\Tests\Unit;

use EcDoris\LaravelCas\Auth\CasGuard;
use EcDoris\LaravelCas\Auth\CasUserProvider;
use EcDoris\LaravelCas\Tests\Models\ExternalUser;
use EcDoris\LaravelCas\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class AuthRegistrationCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('external_users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
    }

    public function test_package_boots_and_resolves_the_laravel_cas_guard()
    {
        $guard = auth()->guard('laravel-cas');

        $this->assertInstanceOf(CasGuard::class, $guard);
    }

    public function test_the_laravel_cas_user_provider_resolves_with_the_configured_model()
    {
        config(['auth.providers.laravel-cas.model' => ExternalUser::class]);

        $provider = $this->app->make('auth')->createUserProvider('laravel-cas');

        $this->assertInstanceOf(CasUserProvider::class, $provider);

        $user = $provider->retrieveByCredentials([
            'user' => 'external-user',
            'attributes' => [
                'email' => 'external.user@example.com',
                'firstName' => 'External',
                'lastName' => 'User',
            ],
        ]);

        $this->assertInstanceOf(ExternalUser::class, $user);
        $this->assertDatabaseHas('external_users', [
            'email' => 'external.user@example.com',
        ]);
    }
}

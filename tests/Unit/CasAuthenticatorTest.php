<?php

declare(strict_types=1);

namespace EcDoris\LaravelCas\Tests\Unit;

use App\Models\User;
use EcDoris\LaravelCas\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class CasAuthenticatorTest extends TestCase
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

        Route::middleware(['web', 'cas.auth'])->get('/protected', static fn () => 'protected');
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
    }

    public function test_it_redirects_unauthenticated_users_to_the_login_route()
    {
        $response = $this->get('/protected');

        $response->assertRedirect(route('laravel-cas-login'));
    }

    public function test_it_allows_authenticated_users_to_access_the_route()
    {
        $user = User::factory()->create();
        auth('laravel-cas')->setUser($user);

        $response = $this->get('/protected');

        $response->assertOk();
        $response->assertSee('protected');
    }
}

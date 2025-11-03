<?php

declare(strict_types=1);

namespace EcDoris\LaravelCas\Tests\Unit;

use App\Models\User;
use EcDoris\LaravelCas\Auth\CasGuard;
use EcDoris\LaravelCas\Auth\CasUserProvider;
use EcDoris\LaravelCas\Tests\TestCase;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Session\Session;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CasGuardTest extends TestCase
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
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
    }

    private function getCasGuard(?UserProvider $provider = null): CasGuard
    {
        $request = $this->app->make(Request::class);
        $session = $this->app->make(Session::class);
        $provider = $provider ?? $this->app->make(CasUserProvider::class);

        return new CasGuard($provider, $request, $session);
    }

    public function test_it_can_masquerade_as_a_user_in_non_production()
    {
        config(['app.env' => 'local']);
        config(['laravel-cas.masquerade' => 'masquerade@example.com']);

        $guard = $this->getCasGuard();
        $user = $guard->masquerade();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('masquerade@example.com', $user->email);
        $this->assertTrue($guard->check());
        $this->assertEquals($user->id, $guard->id());
        $this->assertDatabaseHas('users', ['email' => 'masquerade@example.com']);
    }

    public function test_it_throws_exception_when_masquerading_in_production()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Masquerade cannot be used in a production environment.');

        config(['app.env' => 'production']);
        config(['laravel-cas.masquerade' => 'masquerade@example.com']);

        $guard = $this->getCasGuard();
        $guard->masquerade();
    }

    public function test_it_can_logout_a_user()
    {
        $user = User::factory()->create();
        $guard = $this->getCasGuard();

        $guard->setUser($user);

        $this->assertTrue($guard->check());

        $guard->logout();

        $this->assertFalse($guard->check());
        $this->assertNull($guard->user());
    }

    public function test_it_can_attempt_to_authenticate_a_user()
    {
        $user = User::factory()->make(); // In-memory user
        $credentials = ['user' => 'test'];

        $provider = $this->createMock(CasUserProvider::class);
        $provider->expects($this->once())
            ->method('retrieveByCredentials')
            ->with($credentials)
            ->willReturn($user);

        // After a successful attempt, the guard will use retrieveCasUser to get the user
        $provider->expects($this->any())
            ->method('retrieveCasUser')
            ->willReturn($user);

        $guard = $this->getCasGuard($provider);
        $result = $guard->attempt($credentials);

        $this->assertSame($user, $result);
        $this->assertTrue($guard->check());
        $this->assertEquals($user->id, $guard->id());
    }

    public function test_it_correctly_reports_user_state()
    {
        $user = User::factory()->create();
        $loggedIn = false;

        $provider = $this->createMock(CasUserProvider::class);
        $provider->method('retrieveCasUser')
            ->willReturnCallback(function () use (&$loggedIn, $user) {
                return $loggedIn ? $user : null;
            });

        $guard = $this->getCasGuard($provider);

        // Test logged-out state
        $this->assertTrue($guard->guest());
        $this->assertFalse($guard->check());
        $this->assertFalse($guard->hasUser());
        $this->assertNull($guard->id());

        // Log in a user
        $guard->setUser($user);
        $loggedIn = true;

        // Test logged-in state
        $this->assertFalse($guard->guest());
        $this->assertTrue($guard->check());
        $this->assertTrue($guard->hasUser());
        $this->assertEquals($user->id, $guard->id());
    }
}

<?php

declare(strict_types=1);

namespace EcDoris\LaravelCas\Tests\Unit;

use App\Models\User;
use EcPhp\CasLib\Contract\CasInterface;
use EcPhp\CasLib\Contract\Response\Type\ServiceValidate;
use EcDoris\LaravelCas\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Mockery\MockInterface;
use Psr\Http\Message\ServerRequestInterface;

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
        Route::middleware(['web'])->get('/dashboard', static fn () => 'dashboard')->name('dashboard');
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

    public function test_demo_mode_callback_tickets_authenticate_and_redirect_after_login()
    {
        config([
            'app.env' => 'local',
            'laravel-cas.demo_mode' => true,
            'laravel-cas.redirect_login_route' => 'dashboard',
        ]);

        $ticket = 'DEMO_' . urlencode((string) json_encode([
            'email' => 'demo.user@example.com',
            'firstName' => 'Demo',
            'lastName' => 'User',
        ]));

        $response = $this->get('/cas/callback?ticket=' . $ticket);

        $response->assertRedirect(route('dashboard'));
        self::assertTrue(auth('laravel-cas')->check());
        self::assertSame('demo.user@example.com', auth('laravel-cas')->user()?->email);
        $this->assertDatabaseHas('users', [
            'email' => 'demo.user@example.com',
            'name' => 'Demo User',
        ]);
    }

    public function test_callback_route_runs_cas_ticket_validation_instead_of_returning_the_blank_controller_response()
    {
        config([
            'app.env' => 'production',
            'laravel-cas.demo_mode' => false,
            'laravel-cas.redirect_login_route' => 'dashboard',
        ]);

        $serviceValidateResponse = Mockery::mock(ServiceValidate::class);
        $serviceValidateResponse
            ->shouldReceive('getCredentials')
            ->once()
            ->andReturn([
                'user' => 'cas.user@example.com',
                'attributes' => [
                    'email' => 'cas.user@example.com',
                    'firstName' => 'Cas',
                    'lastName' => 'User',
                ],
            ]);

        $this->mock(CasInterface::class, function (MockInterface $mock) use ($serviceValidateResponse): void {
            $mock
                ->shouldReceive('supportAuthentication')
                ->once()
                ->with(Mockery::type(ServerRequestInterface::class))
                ->andReturn(true);

            $mock
                ->shouldReceive('requestTicketValidation')
                ->once()
                ->with(Mockery::type(ServerRequestInterface::class))
                ->andReturn($serviceValidateResponse);
        });

        $response = $this->get('/cas/callback?ticket=ST-123');

        $response->assertRedirect(route('dashboard'));
        self::assertTrue(auth('laravel-cas')->check());
        self::assertSame('cas.user@example.com', auth('laravel-cas')->user()?->email);
        $this->assertDatabaseHas('users', [
            'email' => 'cas.user@example.com',
            'name' => 'Cas User',
        ]);
    }

    public function test_login_and_logout_package_routes_are_still_bypassed_when_the_authenticator_is_global()
    {
        $this->app['router']->pushMiddlewareToGroup('web', \EcDoris\LaravelCas\Middleware\CasAuthenticator::class);

        $loginResponse = $this->get('/login');
        $logoutResponse = $this->get('/logout');

        $loginResponse->assertRedirect(
            'https://webgate.ec.europa.eu/cas/login?service=http%3A%2F%2Flocalhost%2Fcas%2Fcallback'
        );
        $logoutResponse->assertRedirect(
            'https://webgate.ec.europa.eu/cas/logout?service=http%3A%2F%2Flocalhost'
        );
    }
}

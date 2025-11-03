<?php

declare(strict_types=1);

namespace EcDoris\LaravelCas\Tests\Unit;

use App\Models\User;
use EcDoris\LaravelCas\Auth\CasUserProvider;
use EcDoris\LaravelCas\Tests\TestCase;
use Illuminate\Contracts\Session\Session;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class CasUserProviderTest extends TestCase
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
            $table->string('departmentNumber')->nullable();
            $table->string('department_number')->nullable();
            $table->string('organisation')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
    }

    private function getCasUserProvider(): CasUserProvider
    {
        $session = $this->app->make(Session::class);
        return new CasUserProvider($session);
    }

    public function test_it_creates_a_new_user_from_cas_credentials()
    {
        $provider = $this->getCasUserProvider();
        $credentials = [
            'user' => 'testuser',
            'attributes' => [
                'email' => 'test.user@example.com',
                'firstName' => 'test',
                'lastName' => 'user',
            ],
        ];

        $user = $provider->retrieveByCredentials($credentials);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', [
            'email' => 'test.user@example.com',
        ]);
        $this->assertEquals('Test User', $user->name);
    }

    public function test_it_retrieves_an_existing_user()
    {
        User::factory()->create(['email' => 'existing.user@example.com']);

        $provider = $this->getCasUserProvider();
        $credentials = [
            'user' => 'existinguser',
            'attributes' => [
                'email' => 'existing.user@example.com',
                'firstName' => 'Existing',
                'lastName' => 'User',
            ],
        ];

        $user = $provider->retrieveByCredentials($credentials);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseCount('users', 1);
        $this->assertEquals('existing.user@example.com', $user->email);
    }

    public function test_it_returns_null_if_email_is_missing()
    {
        $provider = $this->getCasUserProvider();
        $credentials = [
            'user' => 'testuser',
            'attributes' => [
                'firstName' => 'Test',
                'lastName' => 'User',
            ],
        ];

        $user = $provider->retrieveByCredentials($credentials);

        $this->assertNull($user);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_it_assigns_department_number_when_fillable()
    {
        $provider = $this->getCasUserProvider();
        $credentials = [
            'user' => 'testuser',
            'attributes' => [
                'email' => 'test.user@example.com',
                'firstName' => 'Test',
                'lastName' => 'User',
                'departmentNumber' => 'DPT123',
            ],
        ];

        $user = $provider->retrieveByCredentials($credentials);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', [
            'email' => 'test.user@example.com',
            'departmentNumber' => 'DPT123',
            'department_number' => 'DPT123',
            'organisation' => 'DPT123',
        ]);
    }
}
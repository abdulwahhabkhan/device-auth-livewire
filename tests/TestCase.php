<?php

declare(strict_types=1);

namespace AbdulWahhabKhan\DeviceAuth\Livewire\Tests;

use AbdulWahhabKhan\DeviceAuth\DeviceAuth;
use AbdulWahhabKhan\DeviceAuth\DeviceAuthServiceProvider;
use AbdulWahhabKhan\DeviceAuth\Enums\DeviceStatus;
use AbdulWahhabKhan\DeviceAuth\Livewire\DeviceAuthLivewireServiceProvider;
use AbdulWahhabKhan\DeviceAuth\Livewire\Tests\Support\User;
use AbdulWahhabKhan\DeviceAuth\Models\Device;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            DeviceAuthServiceProvider::class,
            DeviceAuthLivewireServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Livewire::test() sends its requests without middleware, so no
        // StartSession: hand every new request the test session store.
        $this->app->rebinding('request', static function (Application $app, Request $request): void {
            if (! $request->hasSession()) {
                $request->setLaravelSession($app->make('session.store'));
            }
        });
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('device-auth.rp_id', 'localhost');
        $app['config']->set('device-auth.home', '/dashboard');
        $app['config']->set('device-auth.notifications', []);
        $app['config']->set('app.url', 'https://localhost');
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_superadmin')->default(false);
            $table->timestamps();
        });
    }

    protected function defineRoutes($router): void
    {
        Route::middleware(['web', 'auth', 'device.verified'])->get('/dashboard', fn (): string => 'dashboard');
        Route::middleware('web')->get('/login', fn (): string => 'login')->name('login');
    }

    /** @param array<string, mixed> $attributes */
    protected function device(array $attributes = []): Device
    {
        return Device::query()->create($attributes + [
            'name' => 'Reception PC',
            'status' => DeviceStatus::Pending,
            'credential_id' => bin2hex(random_bytes(16)),
            'public_key' => 'test',
            'request_ip' => '10.0.0.5',
            'device_type' => 'desktop',
            'os' => 'Windows 10',
            'browser' => 'Chrome 142',
        ]);
    }

    /** @param array<string, mixed> $attributes */
    protected function user(array $attributes = []): User
    {
        return User::query()->create($attributes + [
            'name' => 'Priya Shah',
            'email' => uniqid().'@example.test',
            'password' => bcrypt('password'),
        ]);
    }

    /**
     * Sign in as an administrator on a verified device.
     *
     * @return array{0: User, 1: Device}
     */
    protected function adminOnDevice(bool $recentlyConfirmed = true): array
    {
        $admin = $this->user(['is_superadmin' => true, 'name' => 'Admin']);
        $device = $this->device(['status' => DeviceStatus::Active, 'name' => 'Admin PC']);

        $this->actingAs($admin);
        $this->withSession([
            DeviceAuth::SESSION_DEVICE => $device->id,
            DeviceAuth::SESSION_USER => (string) $admin->id,
            DeviceAuth::SESSION_CONFIRMED_AT => $recentlyConfirmed ? now()->getTimestamp() : 0,
        ]);
        session()->put([
            DeviceAuth::SESSION_DEVICE => $device->id,
            DeviceAuth::SESSION_USER => (string) $admin->id,
            DeviceAuth::SESSION_CONFIRMED_AT => $recentlyConfirmed ? now()->getTimestamp() : 0,
        ]);

        return [$admin, $device];
    }
}

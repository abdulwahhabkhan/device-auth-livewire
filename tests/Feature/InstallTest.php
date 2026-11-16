<?php

declare(strict_types=1);

namespace AbdulWahhabKhan\DeviceAuth\Livewire\Tests\Feature;

use AbdulWahhabKhan\DeviceAuth\Livewire\Components\Admin\DeviceShow as PackageDeviceShow;
use AbdulWahhabKhan\DeviceAuth\Livewire\Tests\TestCase;
use App\Livewire\DeviceAuth\Admin\DeviceShow;
use Illuminate\Filesystem\Filesystem;

final class InstallTest extends TestCase
{
    public function test_install_publishes_the_config_and_optionally_the_views(): void
    {
        $files = new Filesystem;

        try {
            $this->artisan('device-auth:livewire-install', ['--views' => true])->assertSuccessful();

            $this->assertFileExists(config_path('device-auth-livewire.php'));
            $this->assertFileExists(resource_path('views/vendor/device-auth/livewire/verify.blade.php'));
        } finally {
            $files->delete(config_path('device-auth-livewire.php'));
            $files->deleteDirectory(resource_path('views/vendor/device-auth'));
        }
    }

    public function test_published_component_stubs_extend_the_package_components(): void
    {
        $this->assertTrue(is_subclass_of(DeviceShow::class, PackageDeviceShow::class));
    }
}

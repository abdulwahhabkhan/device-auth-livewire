<?php

declare(strict_types=1);

namespace AbdulWahhabKhan\DeviceAuth\Livewire\Tests\Feature;

use AbdulWahhabKhan\DeviceAuth\Enums\DeviceStatus;
use AbdulWahhabKhan\DeviceAuth\Livewire\Assets;
use AbdulWahhabKhan\DeviceAuth\Livewire\Components\Admin\DeviceIndex;
use AbdulWahhabKhan\DeviceAuth\Livewire\Components\Admin\DeviceShow;
use AbdulWahhabKhan\DeviceAuth\Livewire\Components\Confirm;
use AbdulWahhabKhan\DeviceAuth\Livewire\Components\Verify;
use AbdulWahhabKhan\DeviceAuth\Livewire\Tests\TestCase;

final class PagesTest extends TestCase
{
    public function test_the_verify_page_renders_the_livewire_component_with_the_browser_script(): void
    {
        $this->actingAs($this->user(['email' => 'priya@example.test']))
            ->get(route('device-auth.verify'))
            ->assertOk()
            ->assertHeader('Accept-CH')
            ->assertSeeLivewire(Verify::class)
            ->assertSee('deviceAuthVerify', false)
            ->assertSee('data-device-auth', false)
            ->assertSee('Signed in as priya@example.test')
            ->assertSee(str_replace('/', '\\/', route('device-auth.verify.options')), false);
    }

    public function test_the_confirm_page_renders_for_a_verified_device(): void
    {
        $this->adminOnDevice();

        $this->get(route('device-auth.confirm'))
            ->assertOk()
            ->assertSeeLivewire(Confirm::class)
            ->assertSee('Admin PC');
    }

    public function test_admin_pages_render_their_components(): void
    {
        $this->adminOnDevice();
        $pending = $this->device(['name' => 'Ward tablet']);

        $this->get(route('device-auth.admin.index'))
            ->assertOk()
            ->assertSeeLivewire(DeviceIndex::class)
            ->assertSee('Ward tablet');

        $this->get(route('device-auth.admin.show', $pending))
            ->assertOk()
            ->assertSeeLivewire(DeviceShow::class)
            ->assertSee($pending->reference())
            ->assertSee('deviceAuthConfirmModal', false);
    }

    public function test_non_admins_cannot_open_the_admin_pages(): void
    {
        $user = $this->user();
        $device = $this->device(['status' => DeviceStatus::Active]);

        $this->actingAs($user)
            ->withSession(['device_auth.device_id' => $device->id, 'device_auth.user_id' => (string) $user->id])
            ->get(route('device-auth.admin.index'))
            ->assertForbidden();
    }

    public function test_the_built_script_ships_with_the_package(): void
    {
        $this->assertFileExists(Assets::path());
        $this->assertStringContainsString('deviceAuthConfirmModal', (string) Assets::scripts());
    }
}

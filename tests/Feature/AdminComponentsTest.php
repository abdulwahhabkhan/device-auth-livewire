<?php

declare(strict_types=1);

namespace AbdulWahhabKhan\DeviceAuth\Livewire\Tests\Feature;

use AbdulWahhabKhan\DeviceAuth\Enums\DeviceStatus;
use AbdulWahhabKhan\DeviceAuth\Livewire\Components\Admin\DeviceIndex;
use AbdulWahhabKhan\DeviceAuth\Livewire\Components\Admin\DeviceShow;
use AbdulWahhabKhan\DeviceAuth\Livewire\Tests\TestCase;
use Livewire\Livewire;

final class AdminComponentsTest extends TestCase
{
    public function test_the_index_filters_by_status_and_search(): void
    {
        $this->adminOnDevice();
        $this->device(['name' => 'Reception PC']);
        $this->device(['name' => 'Ward tablet']);
        $this->device(['name' => 'Old laptop', 'status' => DeviceStatus::Revoked]);

        Livewire::test(DeviceIndex::class)
            ->assertSee('Reception PC')
            ->assertSee('Ward tablet')
            ->assertDontSee('Old laptop')
            ->set('search', 'ward')
            ->assertSee('Ward tablet')
            ->assertDontSee('Reception PC')
            ->set('search', '')
            ->call('show', 'revoked')
            ->assertSet('status', 'revoked')
            ->assertSee('Old laptop')
            ->assertDontSee('Ward tablet');
    }

    public function test_approving_a_pending_device(): void
    {
        [$admin] = $this->adminOnDevice();
        $pending = $this->device(['name' => 'Ward tablet']);

        Livewire::test(DeviceShow::class, ['device' => $pending])
            ->call('open', 'approve')
            ->assertSet('action', 'approve')
            ->assertSet('name', 'Ward tablet')
            ->set('name', 'Ward tablet 3')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('action', null)
            ->assertSee('Ward tablet 3 approved.');

        $pending->refresh();
        $this->assertSame(DeviceStatus::Active, $pending->status);
        $this->assertSame('Ward tablet 3', $pending->name);
        $this->assertSame($admin->id, (int) $pending->reviewed_by);
    }

    public function test_rejecting_needs_a_reason(): void
    {
        $this->adminOnDevice();
        $pending = $this->device();

        Livewire::test(DeviceShow::class, ['device' => $pending])
            ->call('open', 'reject')
            ->call('submit')
            ->assertHasErrors('review_note')
            ->set('reviewNote', 'Unknown laptop')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertSame(DeviceStatus::Rejected, $pending->refresh()->status);
    }

    public function test_the_current_device_cannot_be_revoked_but_others_can(): void
    {
        [, $current] = $this->adminOnDevice();
        $other = $this->device(['status' => DeviceStatus::Active, 'name' => 'Ward tablet']);

        Livewire::test(DeviceShow::class, ['device' => $current])
            ->call('open', 'revoke')
            ->call('submit')
            ->assertHasErrors('review_note');

        $this->assertSame(DeviceStatus::Active, $current->refresh()->status);

        Livewire::test(DeviceShow::class, ['device' => $other])
            ->call('open', 'revoke')
            ->set('reviewNote', 'Lost')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertSame(DeviceStatus::Revoked, $other->refresh()->status);
    }

    public function test_an_invalid_transition_is_shown_as_an_error(): void
    {
        $this->adminOnDevice();
        $pending = $this->device();

        $component = Livewire::test(DeviceShow::class, ['device' => $pending])->call('open', 'reject');

        $pending->forceFill(['status' => DeviceStatus::Active])->save();

        $component->set('reviewNote', 'Too late')->call('submit')->assertHasErrors('device');
    }

    public function test_a_stale_confirmation_asks_the_browser_and_replays_the_action(): void
    {
        $this->adminOnDevice(recentlyConfirmed: false);
        $pending = $this->device();

        $component = Livewire::test(DeviceShow::class, ['device' => $pending])
            ->call('open', 'approve')
            ->assertDispatched('device-auth:confirm-required')
            ->assertSet('action', null)
            ->assertSet('pendingDeviceConfirmation', ['method' => 'open', 'arguments' => ['approve']]);

        // Without a confirmation the replay does nothing.
        $component->dispatch('device-auth:confirmed')->assertSet('action', null);

        // The browser confirmed the device (core confirm endpoint).
        session()->put('device_auth.confirmed_at', now()->getTimestamp());
        $component->call('open', 'approve')->assertSet('action', 'approve');
    }

    public function test_the_replay_runs_after_the_device_is_confirmed(): void
    {
        $this->adminOnDevice(recentlyConfirmed: false);
        $pending = $this->device();

        $component = Livewire::test(DeviceShow::class, ['device' => $pending])
            ->call('open', 'reject')
            ->assertDispatched('device-auth:confirm-required');

        session()->put('device_auth.confirmed_at', now()->getTimestamp());

        $component->dispatch('device-auth:confirmed')
            ->assertSet('action', 'reject')
            ->assertSet('pendingDeviceConfirmation', null);
    }

    public function test_only_administrators_on_a_verified_device_can_use_the_components(): void
    {
        $this->actingAs($this->user());

        Livewire::test(DeviceIndex::class)->assertForbidden();
    }
}

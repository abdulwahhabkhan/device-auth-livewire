<?php

declare(strict_types=1);

namespace AbdulWahhabKhan\DeviceAuth\Livewire\Components\Admin;

use AbdulWahhabKhan\DeviceAuth\DeviceAuth;
use AbdulWahhabKhan\DeviceAuth\Models\Device;
use Illuminate\Support\Facades\Gate;

/**
 * Every request a component handles (the first render and each Livewire
 * update) must come from an administrator on a verified device.
 */
trait AuthorizesAdmins
{
    public function bootAuthorizesAdmins(): void
    {
        abort_unless(Gate::allows(DeviceAuth::ABILITY), 403);
        abort_if(DeviceAuth::enabled() && ! DeviceAuth::currentDevice(request()) instanceof Device, 403);
    }
}

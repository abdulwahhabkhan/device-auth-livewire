<?php

declare(strict_types=1);

namespace AbdulWahhabKhan\DeviceAuth\Livewire\Components\Concerns;

use AbdulWahhabKhan\DeviceAuth\DeviceAuth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

/**
 * Livewire version of the "device.confirm" middleware for component actions.
 *
 *     public function delete(): void
 *     {
 *         if (! $this->deviceConfirmed('delete')) {
 *             return;   // the confirm dialog opens; delete() runs again afterwards
 *         }
 *         // …
 *     }
 *
 * Render <x-device-auth::confirm-modal /> once on the page (the admin screens
 * already do).
 */
trait ConfirmsDevice
{
    /** @var array{method: string, arguments: list<mixed>}|null */
    #[Locked]
    public ?array $pendingDeviceConfirmation = null;

    /**
     * True when the device was confirmed recently. Otherwise asks the browser
     * to confirm and remembers the call to replay once it has.
     *
     * @param  list<mixed>  $arguments
     */
    protected function deviceConfirmed(string $method, array $arguments = []): bool
    {
        if (! DeviceAuth::enabled() || DeviceAuth::recentlyConfirmed(request())) {
            return true;
        }

        $this->pendingDeviceConfirmation = ['method' => $method, 'arguments' => $arguments];
        $this->dispatch('device-auth:confirm-required', source: $this->getId());

        return false;
    }

    #[On('device-auth:confirmed')]
    public function resumeAfterDeviceConfirmation(): void
    {
        $pending = $this->pendingDeviceConfirmation;
        $this->pendingDeviceConfirmation = null;

        if ($pending === null || ! DeviceAuth::recentlyConfirmed(request()) || ! method_exists($this, $pending['method'])) {
            return;
        }

        $this->{$pending['method']}(...$pending['arguments']);
    }
}

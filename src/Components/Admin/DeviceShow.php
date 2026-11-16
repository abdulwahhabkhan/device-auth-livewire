<?php

declare(strict_types=1);

namespace AbdulWahhabKhan\DeviceAuth\Livewire\Components\Admin;

use AbdulWahhabKhan\DeviceAuth\Contracts\ApprovesDevice;
use AbdulWahhabKhan\DeviceAuth\Contracts\RejectsDevice;
use AbdulWahhabKhan\DeviceAuth\Contracts\RevokesDevice;
use AbdulWahhabKhan\DeviceAuth\Data\DeviceShowPageData;
use AbdulWahhabKhan\DeviceAuth\DeviceAuth;
use AbdulWahhabKhan\DeviceAuth\Enums\DeviceStatus;
use AbdulWahhabKhan\DeviceAuth\Exceptions\DeviceAuthException;
use AbdulWahhabKhan\DeviceAuth\Livewire\Components\Concerns\ConfirmsDevice;
use AbdulWahhabKhan\DeviceAuth\Models\Device;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * One device: details, recent use, and the approve / reject / revoke dialogs.
 * The administrator re-confirms their own device before each decision.
 */
class DeviceShow extends Component
{
    use AuthorizesAdmins;
    use ConfirmsDevice;

    #[Locked]
    public int $deviceId;

    /** Which dialog is open. */
    #[Locked]
    public ?string $action = null;

    public string $name = '';

    public string $reviewNote = '';

    public ?string $flash = null;

    public function mount(Device $device): void
    {
        $this->deviceId = $device->id;
    }

    /** Open the approve, reject or revoke dialog. */
    public function open(string $action): void
    {
        if (! in_array($action, ['approve', 'reject', 'revoke'], true) || ! $this->deviceConfirmed('open', [$action])) {
            return;
        }

        $this->resetErrorBag();
        $this->name = $this->device()->name;
        $this->reviewNote = '';
        $this->action = $action;
    }

    public function close(): void
    {
        $this->action = null;
        $this->resetErrorBag();
    }

    public function submit(): void
    {
        $action = $this->action;

        if ($action === null || ! $this->deviceConfirmed('submit')) {
            return;
        }

        $device = $this->device();
        $reviewer = auth()->user();
        $current = DeviceAuth::currentDevice(request());
        $note = ['review_note' => $this->reviewNote !== '' ? $this->reviewNote : null];

        try {
            $decision = match ($action) {
                'approve' => resolve(ApprovesDevice::class)->approve($device, $reviewer, ['name' => $this->name] + $note, $current)->status,
                'reject' => resolve(RejectsDevice::class)->reject($device, $reviewer, $note, $current)->status,
                default => resolve(RevokesDevice::class)->revoke($device, $reviewer, $note, $current)->status,
            };
        } catch (DeviceAuthException $e) {
            $this->addError('device', $e->getMessage());

            return;
        }

        $this->action = null;
        $this->flash = match ($decision) {
            DeviceStatus::Active => __('device-auth::messages.approved', ['name' => $device->name]),
            DeviceStatus::Rejected => __('device-auth::messages.rejected', ['name' => $device->name]),
            DeviceStatus::Revoked, DeviceStatus::Pending => __('device-auth::messages.revoked', ['name' => $device->name]),
        };
    }

    public function render(): View
    {
        return view('device-auth::livewire.admin.device-show', [
            'page' => DeviceShowPageData::fromRequest(request(), $this->device())->toArray(),
        ]);
    }

    private function device(): Device
    {
        return Device::query()->findOrFail($this->deviceId);
    }
}

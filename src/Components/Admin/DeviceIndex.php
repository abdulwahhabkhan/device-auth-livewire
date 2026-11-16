<?php

declare(strict_types=1);

namespace AbdulWahhabKhan\DeviceAuth\Livewire\Components\Admin;

use AbdulWahhabKhan\DeviceAuth\Contracts\ListsDevices;
use AbdulWahhabKhan\DeviceAuth\DeviceAuth;
use AbdulWahhabKhan\DeviceAuth\Enums\DeviceStatus;
use AbdulWahhabKhan\DeviceAuth\Models\Device;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * The administrator's device list ("authorized_keys"): one status at a time.
 */
class DeviceIndex extends Component
{
    use AuthorizesAdmins;
    use WithPagination;

    /** @var value-of<DeviceStatus> */
    #[Url]
    public string $status = 'pending';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function show(string $status): void
    {
        $this->status = (DeviceStatus::tryFrom($status) ?? DeviceStatus::Pending)->value;
        $this->resetPage();
    }

    public function render(ListsDevices $lister): View
    {
        $status = DeviceStatus::tryFrom($this->status) ?? DeviceStatus::Pending;

        return view('device-auth::livewire.admin.device-index', [
            'currentStatus' => $status,
            'statuses' => DeviceStatus::cases(),
            'counts' => Device::countsByStatus(),
            'devices' => $lister->list($status, $this->search),
            'flash' => session(DeviceAuth::SESSION_FLASH),
        ]);
    }
}

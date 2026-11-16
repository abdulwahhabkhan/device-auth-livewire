<?php

declare(strict_types=1);

namespace AbdulWahhabKhan\DeviceAuth\Livewire\Components;

use AbdulWahhabKhan\DeviceAuth\Data\ConfirmPageData;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Full-page confirmation, used when a page behind "device.confirm" is opened.
 */
class Confirm extends Component
{
    /** @var array{name: string, reference: string}|null */
    #[Locked]
    public ?array $device = null;

    /** @var array{options: string, confirm: string} */
    #[Locked]
    public array $urls;

    public function mount(ConfirmPageData $data): void
    {
        $page = $data->toArray();

        $this->device = $page['device'];
        $this->urls = $page['urls'];
    }

    public function render(): View
    {
        return view('device-auth::livewire.confirm');
    }
}

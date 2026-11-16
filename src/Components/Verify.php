<?php

declare(strict_types=1);

namespace AbdulWahhabKhan\DeviceAuth\Livewire\Components;

use AbdulWahhabKhan\DeviceAuth\Data\VerifyPageData;
use AbdulWahhabKhan\DeviceAuth\DeviceAuth;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * "Verify this device" after the password. The WebAuthn ceremony runs in
 * the browser (Alpine data "deviceAuthVerify") against the core JSON
 * endpoints; this component only carries the page data.
 *
 * @phpstan-import-type UserSummary from DeviceAuth
 */
class Verify extends Component
{
    /** @var UserSummary|null */
    #[Locked]
    public ?array $user = null;

    #[Locked]
    public ?string $contact = null;

    /** @var array{verifyOptions: string, verify: string, requestOptions: string, request: string, logout: string|null} */
    #[Locked]
    public array $urls;

    public function mount(VerifyPageData $data): void
    {
        $page = $data->toArray();

        $this->user = $page['user'];
        $this->contact = $page['contact'];
        $this->urls = $page['urls'];
    }

    public function render(): View
    {
        return view('device-auth::livewire.verify');
    }
}

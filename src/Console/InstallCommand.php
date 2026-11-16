<?php

declare(strict_types=1);

namespace AbdulWahhabKhan\DeviceAuth\Livewire\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'device-auth:livewire-install')]
class InstallCommand extends Command
{
    protected $signature = 'device-auth:livewire-install
        {--views : Also publish the Blade views for editing}
        {--force : Overwrite files that were already published}';

    protected $description = 'Publish the device-auth Livewire config (and optionally its views)';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        $this->callSilently('vendor:publish', ['--tag' => 'device-auth-livewire-config', '--force' => $force]);

        if ((bool) $this->option('views')) {
            $this->callSilently('vendor:publish', ['--tag' => 'device-auth-livewire-views', '--force' => $force]);
        }

        $this->components->info('Device auth Livewire pages installed.');
        $this->components->bulletList([
            'Let Tailwind see the package views; in <comment>resources/css/app.css</comment> add: <comment>@source \'../../vendor/abdulwahhabkhan/device-auth-livewire/resources/views\';</comment>',
            'Set <comment>admin_layout</comment> in <comment>config/device-auth-livewire.php</comment> to your app layout component (e.g. <comment>layouts.app</comment>)',
            'Add <comment><x-device-auth::confirm-modal /></comment> to pages whose components use the <comment>ConfirmsDevice</comment> trait',
            'Rebuild assets: <comment>npm run build</comment>',
        ]);

        return self::SUCCESS;
    }
}

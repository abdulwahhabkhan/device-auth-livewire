<?php

declare(strict_types=1);

namespace AbdulWahhabKhan\DeviceAuth\Livewire;

use AbdulWahhabKhan\DeviceAuth\Data\PageData;
use AbdulWahhabKhan\DeviceAuth\DeviceAuth;
use AbdulWahhabKhan\DeviceAuth\Http\Middleware\EnsureDeviceIsVerified;
use AbdulWahhabKhan\DeviceAuth\Http\Middleware\RequireDeviceConfirmation;
use AbdulWahhabKhan\DeviceAuth\Livewire\Console\InstallCommand;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Component;
use Livewire\Livewire;

class DeviceAuthLivewireServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/device-auth-livewire.php', 'device-auth-livewire');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'device-auth');
        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components', 'device-auth');
        Blade::directive('deviceAuthScripts', static fn (): string => '<?php echo \\'.Assets::class.'::scripts(); ?>');

        view()->composer('device-auth::*', static function (View $view): void {
            $view->with('deviceAuth', DeviceAuth::state());
        });

        $this->registerComponents();
        $this->registerViews();

        // Livewire updates hit /livewire/update, not the page route: re-apply
        // the device middleware there for components rendered behind them.
        Livewire::addPersistentMiddleware([
            EnsureDeviceIsVerified::class,
            RequireDeviceConfirmation::class,
        ]);

        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
            $this->commands([InstallCommand::class]);
        }
    }

    private function registerComponents(): void
    {
        /** @var array<string, class-string<Component>> $components */
        $components = (array) config('device-auth-livewire.components', []);

        foreach ($components as $name => $class) {
            Livewire::component('device-auth.'.$name, $class);
        }
    }

    private function registerViews(): void
    {
        DeviceAuth::verifyView($this->page('device-auth::pages.verify'));
        DeviceAuth::confirmView($this->page('device-auth::pages.confirm'));
        DeviceAuth::deviceIndexView($this->page('device-auth::pages.admin.index'));
        DeviceAuth::deviceShowView($this->page('device-auth::pages.admin.show'));
    }

    /**
     * A page view receives the typed page data as $data.
     *
     * @param  view-string  $view
     * @return Closure(Request, PageData): View
     */
    private function page(string $view): Closure
    {
        return static fn (Request $request, PageData $data): View => view($view, ['data' => $data]);
    }

    private function registerPublishing(): void
    {
        $this->publishes([
            __DIR__.'/../config/device-auth-livewire.php' => config_path('device-auth-livewire.php'),
        ], 'device-auth-livewire-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/device-auth'),
        ], 'device-auth-livewire-views');

        $this->publishes([
            __DIR__.'/../stubs/components' => app_path('Livewire/DeviceAuth'),
        ], 'device-auth-livewire-components');
    }
}

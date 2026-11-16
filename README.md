# abdulwahhabkhan/device-auth-livewire

Livewire + Alpine screens for [`abdulwahhabkhan/device-auth`](https://github.com/abdulwahhabkhan/device-auth): only approved devices can use your Laravel app, each proving itself with a passkey (WebAuthn) stored on the device.

The core package holds all the logic (actions, routes, middleware, WebAuthn). This package only draws the pages:

| Screen | Route | Component |
|---|---|---|
| Verify this device / request access | `device-auth.verify` | `device-auth.verify` |
| Confirm this device | `device-auth.confirm` | `device-auth.confirm` |
| Device list | `device-auth.admin.index` | `device-auth.admin.device-index` |
| Device details, approve / reject / revoke | `device-auth.admin.show` | `device-auth.admin.device-show` |

Pages use plain Tailwind utilities (neutral palette with `dark:` variants) and look the same as the React package.

## Requirements

PHP 8.3+, Laravel 13, Livewire 3.6+ or 4, Tailwind CSS. WebAuthn needs **https** (or `localhost`).

## Installation

```bash
composer require abdulwahhabkhan/device-auth abdulwahhabkhan/device-auth-livewire
php artisan device-auth:install            # core: config, actions, service provider
php artisan device-auth:livewire-install   # this package: config (add --views to edit the Blade views)
php artisan migrate
```

Tell Tailwind about the package views, in `resources/css/app.css` (Tailwind 4):

```css
@source '../../vendor/abdulwahhabkhan/device-auth-livewire/resources/views';
```

Then `npm run build`. The browser script ships prebuilt; nothing is added to your JavaScript.

Protect routes as described in the core README:

```php
Route::middleware(['auth', 'device.verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});
```

## Layouts

`config/device-auth-livewire.php`:

```php
'layout' => 'device-auth::layouts.guest',   // verify + confirm: full-screen, no app chrome
'admin_layout' => 'layouts.app',            // device admin: your app layout, e.g. <x-layouts.app>
'vite' => ['resources/css/app.css'],        // loaded by the package layouts
```

Each value is a Blade component name. Layouts receive a `title` attribute.

## Confirming the device in your own components

The `device.confirm` middleware protects routes. For Livewire actions use the trait, which asks the browser to confirm and then replays the call:

```php
use AbdulWahhabKhan\DeviceAuth\Livewire\Components\Concerns\ConfirmsDevice;

class Payroll extends Component
{
    use ConfirmsDevice;

    public function export(): void
    {
        if (! $this->deviceConfirmed('export')) {
            return; // the confirm dialog opens; export() runs again once the device is confirmed
        }

        // …
    }
}
```

Render the dialog once on the page:

```blade
<x-device-auth::confirm-modal />
```

## Customising

- **Behaviour**: change the published core actions in `app/Actions/DeviceAuth`. The Livewire screens call the same actions.
- **Markup**: `php artisan vendor:publish --tag=device-auth-livewire-views` copies the views to `resources/views/vendor/device-auth`.
- **Component classes**: `php artisan vendor:publish --tag=device-auth-livewire-components` copies subclasses to `app/Livewire/DeviceAuth`. Point `components` in the config at them.
- **Text**: core messages come from `lang/vendor/device-auth` (`--tag=device-auth-lang`).

| Publish tag | Files |
|---|---|
| `device-auth-livewire-config` | `config/device-auth-livewire.php` |
| `device-auth-livewire-views` | `resources/views/vendor/device-auth` |
| `device-auth-livewire-components` | `app/Livewire/DeviceAuth` |

## Security notes

- Admin components check the `manage-device-auth` gate and a verified device on every request, including Livewire updates.
- The package registers `device.verified` and `device.confirm` as Livewire persistent middleware, so updates to a component on a protected page are checked like the page itself.
- Approve / reject / revoke require a recent device confirmation (default 10 minutes, `device-auth.confirm_timeout`).

## Development

```bash
composer install
composer test        # pint --test, rector --dry-run, phpstan (level 7), phpunit
npm install
npm run types        # tsc --noEmit (strict)
npm run build        # rebuilds dist/device-auth.js, commit the result
```

### Developing against a local core

The package installs `abdulwahhabkhan/device-auth` from Packagist. To work on both side by side, point Composer at your checkout (don't commit this):

```bash
composer config repositories.core '{"type": "path", "url": "../laravel-device-auth", "options": {"versions": {"abdulwahhabkhan/device-auth": "0.2.0"}}}'
composer update abdulwahhabkhan/device-auth
```

## License

MIT. See [LICENSE](LICENSE).

<?php

declare(strict_types=1);

use AbdulWahhabKhan\DeviceAuth\Livewire\Components\Admin\DeviceIndex;
use AbdulWahhabKhan\DeviceAuth\Livewire\Components\Admin\DeviceShow;
use AbdulWahhabKhan\DeviceAuth\Livewire\Components\Confirm;
use AbdulWahhabKhan\DeviceAuth\Livewire\Components\Verify;

return [

    /*
    |--------------------------------------------------------------------------
    | Layouts
    |--------------------------------------------------------------------------
    |
    | Blade components that wrap the pages. "layout" is used for the verify
    | and confirm screens, which draw their own full-screen frame, so a bare
    | HTML document is enough. "admin_layout" wraps the device admin screens:
    | point it at your app layout, e.g. "layouts.app" for <x-layouts.app>.
    |
    */

    'layout' => 'device-auth::layouts.guest',

    'admin_layout' => 'device-auth::layouts.app',

    /*
    |--------------------------------------------------------------------------
    | Vite entry points
    |--------------------------------------------------------------------------
    |
    | Loaded by the package layouts when the app has a Vite build (or a running
    | dev server). Your CSS must scan the package views; see the README.
    |
    */

    'vite' => ['resources/css/app.css'],

    /*
    |--------------------------------------------------------------------------
    | Components
    |--------------------------------------------------------------------------
    |
    | The Livewire classes behind each screen. Publish them with
    | `php artisan vendor:publish --tag=device-auth-livewire-components`
    | and point these entries at your copies to change their behaviour.
    |
    */

    'components' => [
        'verify' => Verify::class,
        'confirm' => Confirm::class,
        'admin.device-index' => DeviceIndex::class,
        'admin.device-show' => DeviceShow::class,
    ],

];

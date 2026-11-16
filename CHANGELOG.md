# Changelog

All notable changes to `abdulwahhabkhan/device-auth-livewire` are documented here.
This project follows [Semantic Versioning](https://semver.org).

## 0.2.0 - 2026-11-16

### Changed (breaking)
- Requires **Laravel 13**, **PHP 8.3+** and `abdulwahhabkhan/device-auth` ^0.2.
- Requires `laravel/framework` instead of individual `illuminate/*` packages.
- `composer.json` no longer carries a path repository to the core; CI and installs use Packagist.

## 0.1.0 - 2026-11-15

First release.

- Livewire + Alpine screens for `abdulwahhabkhan/device-auth` 0.1: verify device, request access, full-page confirm, device list and device details.
- Registers the core page views through `DeviceAuth::verifyView()`, `confirmView()`, `deviceIndexView()` and `deviceShowView()`.
- Admin decisions run through the core actions (`ApprovesDevice`, `RejectsDevice`, `RevokesDevice`, `ListsDevices`), so customised actions apply here too.
- `ConfirmsDevice` trait and `<x-device-auth::confirm-modal />`: the Livewire version of the `device.confirm` middleware for your own components.
- Prebuilt browser script (`dist/device-auth.js`, strict TypeScript source in `resources/ts`), loaded with `@deviceAuthScripts`. No build step in your app.
- Publishable config, views and component classes.

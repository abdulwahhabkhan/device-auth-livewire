{{--
    Confirm-this-device dialog for Livewire actions that use the ConfirmsDevice
    trait. Render it once per page; it opens on "device-auth:confirm-required".
--}}
@php($state = \AbdulWahhabKhan\DeviceAuth\DeviceAuth::state())
@once
    @deviceAuthScripts
@endonce
<div
    wire:ignore
    x-data="deviceAuthConfirmModal(@js(['urls' => ['options' => $state?->confirmOptionsUrl, 'confirm' => $state?->confirmUrl]]))"
    x-show="open"
    x-cloak
    x-on:keydown.escape.window="close()"
    class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
    role="dialog"
    aria-modal="true"
    aria-labelledby="device-auth-confirm-title"
>
    <div class="absolute inset-0 bg-neutral-950/50" x-on:click="close()"></div>
    <div class="relative grid w-full max-w-md gap-4 rounded-lg bg-white p-6 text-neutral-900 shadow-xl dark:bg-neutral-900 dark:text-neutral-100">
        <h2 id="device-auth-confirm-title" class="text-lg font-semibold">Confirm this device</h2>
        <p class="text-sm text-neutral-600 dark:text-neutral-400">
            Use the fingerprint, face or screen lock of
            <strong class="font-medium text-neutral-900 dark:text-neutral-100">{{ $state?->device?->name ?? 'this device' }}</strong>
            to continue.
        </p>
        <template x-if="error">
            <x-device-auth::notice tone="error"><span x-text="error"></span></x-device-auth::notice>
        </template>
        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <x-device-auth::button variant="secondary" x-on:click="close()" x-bind:disabled="busy">Cancel</x-device-auth::button>
            <x-device-auth::button x-on:click="confirm()" x-bind:disabled="busy">
                <span x-text="busy ? 'Waiting for device…' : 'Confirm device'">Confirm device</span>
            </x-device-auth::button>
        </div>
    </div>
</div>

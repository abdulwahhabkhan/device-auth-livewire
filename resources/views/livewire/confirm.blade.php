{{-- Full-page confirmation (Alpine data deviceAuthConfirm). --}}
<div>
    @deviceAuthScripts
    <div wire:ignore x-data="deviceAuthConfirm(@js(['urls' => $urls]))">
        <x-device-auth::shell alpine="busy ? 'waiting' : 'idle'">
            <x-slot:title>Confirm this device</x-slot:title>
            <p>
                This page needs a fresh confirmation. Use the fingerprint, face or screen lock of
                <strong class="font-medium text-neutral-900 dark:text-neutral-100">{{ $device['name'] ?? 'this device' }}</strong>.
            </p>
            <template x-if="error"><x-device-auth::notice tone="error"><span x-text="error"></span></x-device-auth::notice></template>
            <div class="flex flex-col gap-2">
                <x-device-auth::button x-on:click="confirm()" x-bind:disabled="busy" autofocus>
                    <span x-text="busy ? 'Waiting for device…' : 'Confirm device'">Confirm device</span>
                </x-device-auth::button>
                <x-device-auth::button variant="ghost" x-on:click="window.history.back()" x-bind:disabled="busy">Go back</x-device-auth::button>
            </div>
        </x-device-auth::shell>
    </div>
</div>

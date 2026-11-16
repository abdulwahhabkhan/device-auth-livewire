<x-dynamic-component :component="config('device-auth-livewire.layout', 'device-auth::layouts.guest')" title="Verify device">
    <livewire:device-auth.verify :data="$data" />
</x-dynamic-component>

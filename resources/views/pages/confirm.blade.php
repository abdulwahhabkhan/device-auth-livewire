<x-dynamic-component :component="config('device-auth-livewire.layout', 'device-auth::layouts.guest')" title="Confirm device">
    <livewire:device-auth.confirm :data="$data" />
</x-dynamic-component>

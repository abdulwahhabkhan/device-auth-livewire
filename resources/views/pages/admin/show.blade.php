<x-dynamic-component :component="config('device-auth-livewire.admin_layout', 'device-auth::layouts.app')" :title="$data->device->name.' ('.$data->device->reference().')'">
    <livewire:device-auth.admin.device-show :device="$data->device" />
</x-dynamic-component>

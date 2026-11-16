@props(['title' => null])
<x-device-auth::layouts.guest :title="$title">
    <div class="min-h-svh bg-neutral-50 dark:bg-neutral-950">
        {{ $slot }}
    </div>
</x-device-auth::layouts.guest>

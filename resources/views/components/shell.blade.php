{{-- Full-screen frame for the device screens; independent of any app layout. --}}
@props(['state' => 'idle', 'alpine' => null])
<div {{ $attributes->class(['flex min-h-svh flex-col bg-neutral-50 text-neutral-900 dark:bg-neutral-950 dark:text-neutral-100']) }}>
    <main class="flex flex-1 items-center justify-center px-4 py-12">
        <div class="w-full max-w-sm">
            <x-device-auth::mark :state="$state" :alpine="$alpine" />
            <h1 class="mt-6 text-2xl font-semibold tracking-tight text-balance" aria-live="polite">{{ $title }}</h1>
            <div class="mt-3 grid gap-5 text-[0.9375rem] leading-relaxed text-neutral-600 dark:text-neutral-400">
                {{ $slot }}
            </div>
        </div>
    </main>
    @isset($footer)
        <footer class="px-4 pb-8 text-center text-sm text-neutral-500 dark:text-neutral-400">{{ $footer }}</footer>
    @endisset
</div>

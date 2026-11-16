{{--
    The key "slot": one mark whose shape and colour say where the device stands.
    $state is a PHP value, or pass :alpine="'expression'" to follow Alpine state.
--}}
@props(['state' => 'idle', 'alpine' => null])
@php
    $marks = [
        'idle' => ['key', 'bg-neutral-100 text-neutral-900 ring-neutral-200 dark:bg-neutral-800 dark:text-neutral-100 dark:ring-neutral-700'],
        'waiting' => ['key', 'bg-neutral-900 text-white ring-neutral-900 dark:bg-neutral-100 dark:text-neutral-900 dark:ring-neutral-100'],
        'pending' => ['clock', 'bg-amber-100 text-amber-800 ring-amber-200 dark:bg-amber-950 dark:text-amber-300 dark:ring-amber-900'],
        'approved' => ['check', 'bg-emerald-100 text-emerald-800 ring-emerald-200 dark:bg-emerald-950 dark:text-emerald-300 dark:ring-emerald-900'],
        'blocked' => ['block', 'bg-rose-100 text-rose-800 ring-rose-200 dark:bg-rose-950 dark:text-rose-300 dark:ring-rose-900'],
        'unknown' => ['question', 'bg-neutral-100 text-neutral-700 ring-neutral-200 dark:bg-neutral-800 dark:text-neutral-300 dark:ring-neutral-700'],
        'problem' => ['alert', 'bg-rose-100 text-rose-800 ring-rose-200 dark:bg-rose-950 dark:text-rose-300 dark:ring-rose-900'],
    ];
@endphp
<div class="relative size-14">
    @if ($alpine)
        <span x-show="{{ $alpine }} === 'waiting'" x-cloak class="absolute inset-0 rounded-2xl bg-neutral-900/20 motion-safe:animate-ping dark:bg-neutral-100/20" aria-hidden="true"></span>
        @foreach ($marks as $key => [$icon, $classes])
            <div x-show="{{ $alpine }} === '{{ $key }}'" @if ($key !== 'idle') x-cloak @endif class="relative flex size-14 items-center justify-center rounded-2xl ring-1 {{ $classes }}">
                <x-device-auth::icon :name="$icon" class="size-7" />
            </div>
        @endforeach
    @else
        @php([$icon, $classes] = $marks[$state] ?? $marks['idle'])
        @if ($state === 'waiting')
            <span class="absolute inset-0 rounded-2xl bg-neutral-900/20 motion-safe:animate-ping dark:bg-neutral-100/20" aria-hidden="true"></span>
        @endif
        <div class="relative flex size-14 items-center justify-center rounded-2xl ring-1 {{ $classes }}">
            <x-device-auth::icon :name="$icon" class="size-7" />
        </div>
    @endif
</div>

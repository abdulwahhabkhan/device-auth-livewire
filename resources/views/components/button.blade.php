@props(['variant' => 'primary', 'type' => 'button'])
@php
    $variants = [
        'primary' => 'bg-neutral-900 text-white hover:bg-neutral-700 dark:bg-neutral-100 dark:text-neutral-900 dark:hover:bg-neutral-300',
        'secondary' => 'border border-neutral-300 bg-white text-neutral-900 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:hover:bg-neutral-800',
        'danger' => 'bg-rose-700 text-white hover:bg-rose-600 dark:bg-rose-600 dark:hover:bg-rose-500',
        'ghost' => 'text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:text-neutral-100 dark:hover:bg-neutral-800',
    ];
@endphp
<button type="{{ $type }}" {{ $attributes->class(['inline-flex min-h-10 items-center justify-center gap-2 rounded-md px-4 text-sm font-medium transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-neutral-900 disabled:cursor-not-allowed disabled:opacity-50 dark:focus-visible:outline-neutral-100', $variants[$variant] ?? $variants['primary']]) }}>{{ $slot }}</button>

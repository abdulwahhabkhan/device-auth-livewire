@props(['tone' => 'neutral'])
@php
    $tones = [
        'neutral' => 'border-neutral-200 bg-neutral-50 text-neutral-700 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-200',
        'error' => 'border-rose-200 bg-rose-50 text-rose-900 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-200',
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200',
    ];
@endphp
<div role="{{ $tone === 'error' ? 'alert' : 'status' }}" {{ $attributes->class(['rounded-md border px-3 py-2 text-sm', $tones[$tone] ?? $tones['neutral']]) }}>{{ $slot }}</div>

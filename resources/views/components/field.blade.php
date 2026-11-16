@props(['label', 'for', 'hint' => null, 'error' => null])
<div class="grid gap-1.5">
    <label for="{{ $for }}" class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $label }}</label>
    {{ $slot }}
    @if ($error)
        <p class="text-xs text-rose-700 dark:text-rose-400">{{ $error }}</p>
    @elseif ($hint)
        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $hint }}</p>
    @endif
</div>

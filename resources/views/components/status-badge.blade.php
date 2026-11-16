@props(['status'])
@php
    $styles = [
        'pending' => 'bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-200',
        'active' => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200',
        'rejected' => 'bg-rose-100 text-rose-900 dark:bg-rose-950 dark:text-rose-200',
        'revoked' => 'bg-neutral-200 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200',
    ];
    $case = \AbdulWahhabKhan\DeviceAuth\Enums\DeviceStatus::tryFrom((string) $status) ?? \AbdulWahhabKhan\DeviceAuth\Enums\DeviceStatus::Revoked;
@endphp
<span {{ $attributes->class(['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium whitespace-nowrap', $styles[$case->value]]) }}>{{ $case->label() }}</span>

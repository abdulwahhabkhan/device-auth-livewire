<?php

declare(strict_types=1);

namespace AbdulWahhabKhan\DeviceAuth\Livewire\Support;

use Illuminate\Support\Facades\Date;

/**
 * Display helpers for the admin views (same wording as the React pages).
 */
final class Format
{
    private const array DEVICE_TYPES = [
        'desktop' => 'Computer',
        'smartphone' => 'Phone',
        'phablet' => 'Phone',
        'tablet' => 'Tablet',
    ];

    public static function deviceType(?string $type): string
    {
        if ($type === null || $type === '') {
            return 'Unknown device';
        }

        return self::DEVICE_TYPES[$type] ?? ucfirst($type);
    }

    /**
     * "Computer, Windows 10, Chrome 142".
     *
     * @param  array{device_type: string|null, os: string|null, browser: string|null}  $device
     */
    public static function device(array $device): string
    {
        return implode(', ', array_filter(
            [self::deviceType($device['device_type']), $device['os'], $device['browser']],
            static fn (?string $part): bool => $part !== null && $part !== '',
        ));
    }

    public static function dateTime(?string $iso): string
    {
        return $iso === null ? 'Never' : Date::parse($iso)->isoFormat('lll');
    }

    public static function ago(?string $iso): string
    {
        return $iso === null ? 'Never' : Date::parse($iso)->diffForHumans();
    }
}

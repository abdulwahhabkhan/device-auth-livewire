<?php

declare(strict_types=1);

namespace AbdulWahhabKhan\DeviceAuth\Livewire;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;
use RuntimeException;

/**
 * The browser half (WebAuthn + Alpine data), shipped prebuilt in dist/ so
 * apps don't need to add anything to their own JavaScript build.
 */
final class Assets
{
    private static ?string $script = null;

    public static function path(): string
    {
        return __DIR__.'/../dist/device-auth.js';
    }

    public static function scripts(): HtmlString
    {
        if (self::$script === null) {
            $contents = file_get_contents(self::path());

            if ($contents === false) {
                throw new RuntimeException('device-auth-livewire: dist/device-auth.js is missing. Run `npm run build` in the package.');
            }

            self::$script = $contents;
        }

        $nonce = Vite::cspNonce();
        $attributes = is_string($nonce) && $nonce !== '' ? ' nonce="'.e($nonce).'"' : '';

        return new HtmlString(
            '<style'.$attributes.'>[x-cloak]{display:none!important}</style>'
            .'<script data-device-auth'.$attributes.'>'.self::$script.'</script>',
        );
    }
}

@props(['name'])
<svg {{ $attributes->merge(['viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.75', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
    @switch($name)
        @case('key')
            <circle cx="8" cy="15" r="4" /><path d="M10.85 12.15 19 4" /><path d="m16 7 2.5 2.5" /><path d="m18.5 4.5 2 2" />
            @break
        @case('clock')
            <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
            @break
        @case('check')
            <path d="m5 12.5 4.5 4.5L19 7.5" />
            @break
        @case('block')
            <circle cx="12" cy="12" r="9" /><path d="m5.7 5.7 12.6 12.6" />
            @break
        @case('question')
            <circle cx="12" cy="12" r="9" /><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.6v.6" /><path d="M12 17h.01" />
            @break
        @case('alert')
            <path d="M12 4 2.8 19.5h18.4Z" /><path d="M12 10v4" /><path d="M12 17h.01" />
            @break
        @case('chevron-left')
            <path d="m15 18-6-6 6-6" />
            @break
    @endswitch
</svg>

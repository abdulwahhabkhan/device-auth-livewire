@php
    use AbdulWahhabKhan\DeviceAuth\Livewire\Support\Format;

    $tabs = ['pending' => 'Requests', 'active' => 'Approved', 'rejected' => 'Rejected', 'revoked' => 'Revoked'];
    $empty = [
        'pending' => 'No requests waiting. When someone asks for access from a new device, it shows up here.',
        'active' => 'No approved devices yet. Approve a request to let that device open the app.',
        'rejected' => 'No rejected requests.',
        'revoked' => 'No revoked devices.',
    ];
@endphp
<div class="mx-auto grid w-full max-w-5xl gap-6 p-4 text-neutral-900 sm:p-6 dark:text-neutral-100">
    <header class="grid gap-1">
        <h1 class="text-2xl font-semibold tracking-tight">Devices</h1>
        <p class="text-sm text-neutral-600 dark:text-neutral-400">Only devices you approve here can open the app. Each approved device holds its own key.</p>
    </header>

    @if (is_string($flash))
        <x-device-auth::notice tone="success">{{ $flash }}</x-device-auth::notice>
    @endif

    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <nav aria-label="Device status" class="-mx-1 flex gap-1 overflow-x-auto">
            @foreach ($tabs as $value => $label)
                @php($current = $value === $currentStatus->value)
                <button type="button" wire:click="show('{{ $value }}')" @if ($current) aria-current="page" @endif
                    @class([
                        'flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium whitespace-nowrap transition-colors',
                        'bg-neutral-900 text-white dark:bg-neutral-100 dark:text-neutral-900' => $current,
                        'text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:hover:text-neutral-100' => ! $current,
                    ])>
                    {{ $label }}
                    <span @class([
                        'rounded-full px-1.5 text-xs tabular-nums',
                        'bg-amber-200 text-amber-950 dark:bg-amber-900 dark:text-amber-100' => $value === 'pending' && $counts['pending'] > 0 && ! $current,
                        'bg-white/20' => $current,
                        'bg-neutral-200 dark:bg-neutral-800' => ! $current && ! ($value === 'pending' && $counts['pending'] > 0),
                    ])>{{ $counts[$value] }}</span>
                </button>
            @endforeach
        </nav>

        <form wire:submit="$refresh" role="search" class="flex gap-2">
            <label for="device-search" class="sr-only">Search devices</label>
            <input id="device-search" type="search" wire:model.live.debounce.400ms="search" placeholder="Name, reference or IP" class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-1 focus:ring-neutral-900 dark:border-neutral-700 dark:bg-neutral-950 dark:text-neutral-100 dark:focus:border-neutral-100 dark:focus:ring-neutral-100 sm:w-56">
            <x-device-auth::button type="submit" variant="secondary">Search</x-device-auth::button>
        </form>
    </div>

    @if ($devices->isEmpty())
        <div class="rounded-lg border border-dashed border-neutral-300 px-6 py-12 text-center text-sm text-neutral-600 dark:border-neutral-700 dark:text-neutral-400">
            {{ $search !== '' ? 'No devices match “'.$search.'”.' : $empty[$currentStatus->value] }}
        </div>
    @else
        <ul class="divide-y divide-neutral-200 overflow-hidden rounded-lg border border-neutral-200 bg-white dark:divide-neutral-800 dark:border-neutral-800 dark:bg-neutral-900">
            @foreach ($devices->items() as $device)
                @php($row = $device->adminSummary())
                <li wire:key="device-{{ $row['id'] }}">
                    <a href="{{ route('device-auth.admin.show', $row['id']) }}" wire:navigate
                        class="grid gap-1 px-4 py-3 transition-colors hover:bg-neutral-50 focus-visible:bg-neutral-50 focus-visible:outline-none sm:grid-cols-[1fr_auto] sm:items-center sm:gap-4 dark:hover:bg-neutral-800/60 dark:focus-visible:bg-neutral-800/60">
                        <div class="min-w-0">
                            <p class="flex items-baseline gap-2">
                                <span class="truncate font-medium">{{ $row['name'] }}</span>
                                <span class="shrink-0 text-xs text-neutral-500 tabular-nums dark:text-neutral-400">{{ $row['reference'] }}</span>
                            </p>
                            <p class="truncate text-sm text-neutral-600 dark:text-neutral-400">
                                {{ Format::device($row) }}@if ($row['requester']), requested by {{ $row['requester']['name'] }}@endif
                            </p>
                        </div>
                        <div class="text-sm text-neutral-500 sm:text-right dark:text-neutral-400">
                            <p class="tabular-nums">{{ $row['request_ip'] ?? 'No IP' }}</p>
                            <p>{{ $currentStatus->value === 'active' ? 'Last used '.Format::ago($row['last_seen_at']) : 'Requested '.Format::ago($row['created_at']) }}</p>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif

    @if ($devices->lastPage() > 1)
        <div class="flex items-center justify-between text-sm text-neutral-600 dark:text-neutral-400">
            <span class="tabular-nums">{{ $devices->firstItem() }}–{{ $devices->lastItem() }} of {{ $devices->total() }}</span>
            <div class="flex gap-2">
                <x-device-auth::button variant="secondary" wire:click="previousPage" :disabled="$devices->currentPage() <= 1">Previous</x-device-auth::button>
                <x-device-auth::button variant="secondary" wire:click="nextPage" :disabled="$devices->currentPage() >= $devices->lastPage()">Next</x-device-auth::button>
            </div>
        </div>
    @endif
</div>

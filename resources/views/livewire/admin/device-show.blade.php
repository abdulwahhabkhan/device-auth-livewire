@php
    use AbdulWahhabKhan\DeviceAuth\Livewire\Support\Format;

    $device = $page['device'];
    $meta = $device['client_meta'];
    $urls = $page['urls'];
    $requester = $device['requester'];
    $titles = [
        'approve' => ['Approve '.$device['reference'].'?', 'This device will be able to open the app right away.'],
        'reject' => ['Reject '.$device['reference'].'?', 'The person who asked will see your reason on their device.'],
        'revoke' => ['Revoke '.$device['name'].'?', 'Anyone using this device is signed out on their next click, and the device can’t open the app until it’s approved again.'],
    ];
    $row = static fn (mixed $value): string => $value === null || $value === '' ? '' : (string) $value;
@endphp
<div class="mx-auto grid w-full max-w-3xl gap-8 p-4 text-neutral-900 sm:p-6 dark:text-neutral-100">
    <div class="grid gap-4">
        <a href="{{ $urls['index'] }}" wire:navigate class="inline-flex w-fit items-center gap-1 text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100">
            <x-device-auth::icon name="chevron-left" class="size-4" />
            All devices
        </a>

        <header class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="grid gap-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-semibold tracking-tight">{{ $device['name'] }}</h1>
                    <x-device-auth::status-badge :status="$device['status']" />
                </div>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    Reference <span class="font-medium text-neutral-900 tabular-nums dark:text-neutral-100">{{ $device['reference'] }}</span>@if ($device['is_current']), the device you’re using now @endif
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                @if ($device['status'] === 'pending')
                    <x-device-auth::button variant="secondary" wire:click="open('reject')">Reject</x-device-auth::button>
                    <x-device-auth::button wire:click="open('approve')">Approve</x-device-auth::button>
                @elseif (in_array($device['status'], ['rejected', 'revoked'], true))
                    <x-device-auth::button wire:click="open('approve')">Approve</x-device-auth::button>
                @else
                    <x-device-auth::button variant="danger" wire:click="open('revoke')" :disabled="$device['is_current']"
                        :title="$device['is_current'] ? 'Revoke it from another approved device' : null">Revoke</x-device-auth::button>
                @endif
            </div>
        </header>

        @if ($flash)
            <x-device-auth::notice tone="success">{{ $flash }}</x-device-auth::notice>
        @endif
        @error('device')
            <x-device-auth::notice tone="error">{{ $message }}</x-device-auth::notice>
        @enderror
        @if ($device['status'] === 'pending' && $device['backup_eligible'])
            <x-device-auth::notice tone="warning">This key is synced through a password manager (for example iCloud or Google). Other devices signed in to the same account may be able to use it.</x-device-auth::notice>
        @endif
    </div>

    @php
        $sections = [
            'Request' => [
                'Requested by' => $requester ? $requester['name'].($requester['email'] ? ' ('.$requester['email'].')' : '') : '',
                'Requested' => $device['created_at'] ? Format::dateTime($device['created_at']).' ('.Format::ago($device['created_at']).')' : '',
                'Reason' => $row($device['request_note']),
            ],
            'Device' => [
                'Type' => Format::deviceType($device['device_type']),
                'Operating system' => $row($device['os']),
                'Browser' => $row($device['browser']),
                'Model' => $row($device['device_model'] ?? ($meta['model'] ?? null)),
                'Screen' => $row($meta['screen'] ?? null),
                'Time zone' => $row($meta['timezone'] ?? null),
                'Language' => $row($meta['language'] ?? null),
                'User agent' => $row($device['user_agent']),
            ],
            'Network' => [
                'Request IP' => $row($device['request_ip']),
                'Last IP' => $row($device['last_ip']),
                'Last used' => $device['last_seen_at'] ? Format::dateTime($device['last_seen_at']).' ('.Format::ago($device['last_seen_at']).')' : 'Never',
            ],
            'Key' => [
                'Stored in' => $device['authenticator'] ?? 'Unknown authenticator',
                'Sync' => $device['backup_eligible'] ? 'Synced through a password manager' : 'Stays on this device',
            ],
        ];

        if ($device['reviewed_at']) {
            $sections['Review'] = [
                'Decision' => (\AbdulWahhabKhan\DeviceAuth\Enums\DeviceStatus::tryFrom($device['status']) ?? \AbdulWahhabKhan\DeviceAuth\Enums\DeviceStatus::Revoked)->label(),
                'By' => $device['reviewer']['name'] ?? 'Command line',
                'On' => Format::dateTime($device['reviewed_at']),
                'Note' => $row($device['review_note']),
            ];
        }
    @endphp

    @foreach ($sections as $title => $rows)
        <section class="grid gap-3">
            <h2 class="text-base font-semibold">{{ $title }}</h2>
            <dl class="grid gap-x-6 gap-y-2 text-sm sm:grid-cols-[10rem_1fr]">
                @foreach ($rows as $label => $value)
                    <dt class="text-neutral-500 dark:text-neutral-400">{{ $label }}</dt>
                    <dd @class(['min-w-0 break-words text-neutral-900 dark:text-neutral-100', 'text-xs' => $label === 'User agent'])>
                        @if ($value === '')
                            <span class="text-neutral-400">Not recorded</span>
                        @else
                            {{ $value }}
                        @endif
                    </dd>
                @endforeach
            </dl>
        </section>
    @endforeach

    <section class="grid gap-3">
        <h2 class="text-base font-semibold">Recent use</h2>
        @if ($page['logins'] === [])
            <p class="text-sm text-neutral-600 dark:text-neutral-400">This device hasn’t been used yet.</p>
        @else
            <div class="overflow-x-auto rounded-lg border border-neutral-200 dark:border-neutral-800">
                <table class="w-full text-left text-sm">
                    <thead class="bg-neutral-50 text-neutral-600 dark:bg-neutral-900 dark:text-neutral-400">
                        <tr>
                            <th class="px-3 py-2 font-medium">When</th>
                            <th class="px-3 py-2 font-medium">User</th>
                            <th class="px-3 py-2 font-medium">Action</th>
                            <th class="px-3 py-2 font-medium">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                        @foreach ($page['logins'] as $login)
                            <tr wire:key="login-{{ $login['id'] }}">
                                <td class="px-3 py-2 whitespace-nowrap">{{ Format::dateTime($login['created_at']) }}</td>
                                <td class="px-3 py-2">{{ $login['user']['name'] ?? 'Deleted user' }}</td>
                                <td class="px-3 py-2">{{ $login['purpose'] === 'verify' ? 'Signed in' : 'Confirmed' }}</td>
                                <td class="px-3 py-2 tabular-nums">{{ $login['ip'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    @if ($action !== null)
        <div class="fixed inset-0 z-40 flex items-end justify-center p-4 sm:items-center" role="dialog" aria-modal="true" aria-labelledby="device-auth-review-title"
            x-data x-on:keydown.escape.window="$wire.close()">
            <div class="absolute inset-0 bg-neutral-950/50" wire:click="close"></div>
            <form wire:submit="submit" class="relative grid w-full max-w-md gap-4 rounded-lg bg-white p-6 text-neutral-900 shadow-xl dark:bg-neutral-900 dark:text-neutral-100">
                <div class="grid gap-1">
                    <h2 id="device-auth-review-title" class="text-lg font-semibold">{{ $titles[$action][0] }}</h2>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $titles[$action][1] }}</p>
                </div>

                @if ($action !== 'revoke')
                    <dl class="grid grid-cols-[7rem_1fr] gap-y-1.5 rounded-md bg-neutral-50 p-3 text-sm dark:bg-neutral-950">
                        <dt class="text-neutral-500 dark:text-neutral-400">Requested by</dt>
                        <dd>{{ $requester ? $requester['name'].($requester['email'] ? ' ('.$requester['email'].')' : '') : 'Unknown' }}</dd>
                        <dt class="text-neutral-500 dark:text-neutral-400">Device</dt>
                        <dd>{{ Format::device($device) }}</dd>
                        <dt class="text-neutral-500 dark:text-neutral-400">IP address</dt>
                        <dd class="tabular-nums">{{ $device['request_ip'] ?? 'Not recorded' }}</dd>
                        <dt class="text-neutral-500 dark:text-neutral-400">Requested</dt>
                        <dd>{{ Format::dateTime($device['created_at']) }}</dd>
                        <dt class="text-neutral-500 dark:text-neutral-400">Key</dt>
                        <dd>{{ $device['authenticator'] ?? 'Unknown authenticator' }}, {{ $device['backup_eligible'] ? 'synced' : 'stored on this device only' }}</dd>
                    </dl>
                @endif

                @if ($action === 'approve')
                    <x-device-auth::field label="Device name" for="review-name" :error="$errors->first('name')">
                        <input id="review-name" wire:model="name" maxlength="100" class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-1 focus:ring-neutral-900 dark:border-neutral-700 dark:bg-neutral-950 dark:text-neutral-100 dark:focus:border-neutral-100 dark:focus:ring-neutral-100">
                    </x-device-auth::field>
                @endif

                <x-device-auth::field
                    :label="['approve' => 'Note (optional)', 'reject' => 'Reason', 'revoke' => 'Reason (optional)'][$action]"
                    for="review-note"
                    :hint="$action === 'revoke' ? 'Shown on the device.' : null"
                    :error="$errors->first('review_note') ?: ($errors->first('ticket') ?: null)">
                    <textarea id="review-note" wire:model="reviewNote" maxlength="500" @if ($action !== 'approve') autofocus @endif class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-1 focus:ring-neutral-900 dark:border-neutral-700 dark:bg-neutral-950 dark:text-neutral-100 dark:focus:border-neutral-100 dark:focus:ring-neutral-100 min-h-20"></textarea>
                </x-device-auth::field>

                @error('device')
                    <x-device-auth::notice tone="error">{{ $message }}</x-device-auth::notice>
                @enderror

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <x-device-auth::button variant="secondary" wire:click="close" wire:loading.attr="disabled">Cancel</x-device-auth::button>
                    <x-device-auth::button type="submit" :variant="$action === 'approve' ? 'primary' : 'danger'" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="submit">{{ ['approve' => 'Approve device', 'reject' => 'Reject request', 'revoke' => 'Revoke device'][$action] }}</span>
                        <span wire:loading wire:target="submit">{{ ['approve' => 'Approving…', 'reject' => 'Rejecting…', 'revoke' => 'Revoking…'][$action] }}</span>
                    </x-device-auth::button>
                </div>
            </form>
        </div>
    @endif

    <x-device-auth::confirm-modal />
</div>

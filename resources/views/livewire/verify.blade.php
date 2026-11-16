{{-- "Verify this device": WebAuthn runs in the browser (Alpine data deviceAuthVerify). --}}
<div>
    @deviceAuthScripts
    <div wire:ignore x-data="deviceAuthVerify(@js(['contact' => $contact, 'urls' => $urls]))">
        <x-device-auth::shell alpine="mark">
            <x-slot:title><span x-text="title">Verify this device</span></x-slot:title>

            {{-- Browser can't do WebAuthn --}}
            <template x-if="problem">
                <p x-text="problem === 'insecure'
                    ? 'Device keys only work over https. Open the app using its https address.'
                    : 'Use an up-to-date Chrome, Edge, Safari or Firefox on a device with a screen lock (PIN, fingerprint or face).'"></p>
            </template>

            {{-- Start --}}
            <template x-if="!problem && (phase === 'idle' || phase === 'verifying')">
                <div class="grid gap-5">
                    <p>Only approved devices can open this app. Confirm this one with its fingerprint, face or screen lock.</p>
                    <template x-if="error"><x-device-auth::notice tone="error"><span x-text="error"></span></x-device-auth::notice></template>
                    <div class="flex flex-col gap-2">
                        <x-device-auth::button x-on:click="verify()" x-bind:disabled="busy" autofocus>
                            <span x-text="phase === 'verifying' ? 'Waiting for device…' : 'Verify device'">Verify device</span>
                        </x-device-auth::button>
                        <x-device-auth::button variant="ghost" x-on:click="phase = 'request'" x-bind:disabled="busy">New device? Request access</x-device-auth::button>
                    </div>
                </div>
            </template>

            {{-- Request access --}}
            <template x-if="!problem && (phase === 'request' || phase === 'requesting')">
                <div class="grid gap-5">
                    <p>This creates a key that stays on this device. An administrator reviews the request; once approved, this device can open the app.</p>
                    <form x-on:submit.prevent="requestAccess()" class="grid gap-4" novalidate>
                        <div class="grid gap-1.5">
                            <label for="device-name" class="text-sm font-medium text-neutral-900 dark:text-neutral-100">Device name</label>
                            <input id="device-name" x-model="name" maxlength="100" required autofocus placeholder="Reception PC" class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-1 focus:ring-neutral-900 dark:border-neutral-700 dark:bg-neutral-950 dark:text-neutral-100 dark:focus:border-neutral-100 dark:focus:ring-neutral-100">
                            <p x-show="!fieldErrors.name" class="text-xs text-neutral-500 dark:text-neutral-400">How the administrator will recognise it.</p>
                            <p x-show="fieldErrors.name" x-text="fieldErrors.name" x-cloak class="text-xs text-rose-700 dark:text-rose-400"></p>
                        </div>
                        <div class="grid gap-1.5">
                            <label for="device-note" class="text-sm font-medium text-neutral-900 dark:text-neutral-100">Reason (optional)</label>
                            <textarea id="device-note" x-model="note" maxlength="500" placeholder="New laptop for the front desk" class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-1 focus:ring-neutral-900 dark:border-neutral-700 dark:bg-neutral-950 dark:text-neutral-100 dark:focus:border-neutral-100 dark:focus:ring-neutral-100 min-h-20"></textarea>
                            <p x-show="fieldErrors.note" x-text="fieldErrors.note" x-cloak class="text-xs text-rose-700 dark:text-rose-400"></p>
                        </div>
                        <template x-if="error"><x-device-auth::notice tone="error"><span x-text="error"></span></x-device-auth::notice></template>
                        <div class="flex flex-col gap-2">
                            <x-device-auth::button type="submit" x-bind:disabled="busy">
                                <span x-text="phase === 'requesting' ? 'Waiting for device…' : 'Send request'">Send request</span>
                            </x-device-auth::button>
                            <x-device-auth::button variant="ghost" x-on:click="phase = 'idle'" x-bind:disabled="busy">Back</x-device-auth::button>
                        </div>
                    </form>
                </div>
            </template>

            {{-- Waiting for approval --}}
            <template x-if="!problem && phase === 'pending' && device">
                <div class="grid gap-5">
                    <p>
                        The request for <strong class="font-medium text-neutral-900 dark:text-neutral-100" x-text="device.name"></strong>
                        was sent on <span x-text="formatDateTime(device.requested_at)"></span>.
                        Ask your administrator to approve it and give them this reference:
                    </p>
                    <p class="rounded-md border border-dashed border-amber-300 bg-amber-50 px-4 py-3 text-center text-2xl font-semibold tracking-wide text-amber-950 tabular-nums dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-100" x-text="device.reference"></p>
                    <p x-show="contact">Contact: <span class="font-medium text-neutral-900 dark:text-neutral-100" x-text="contact"></span></p>
                    <x-device-auth::button variant="secondary" x-on:click="verify()">Check again</x-device-auth::button>
                </div>
            </template>

            {{-- Rejected / revoked --}}
            <template x-if="!problem && (phase === 'rejected' || phase === 'revoked') && device">
                <div class="grid gap-5">
                    <p x-text="phase === 'rejected'
                        ? `The request for ${device.name} (${device.reference}) was rejected.`
                        : `${device.name} (${device.reference}) can no longer open this app.`"></p>
                    <template x-if="device.review_note">
                        <x-device-auth::notice>Reason: <span x-text="device.review_note"></span></x-device-auth::notice>
                    </template>
                    <p>Contact your administrator if you need access from this device.</p>
                    <p x-show="contact">Contact: <span class="font-medium text-neutral-900 dark:text-neutral-100" x-text="contact"></span></p>
                    <x-device-auth::button variant="secondary" x-show="phase === 'rejected'" x-on:click="phase = 'request'">Request again</x-device-auth::button>
                </div>
            </template>

            {{-- Key belongs to another device --}}
            <template x-if="!problem && phase === 'mismatch'">
                <div class="grid gap-5">
                    <p>The key you chose was approved for a different device. Each device needs its own approval.</p>
                    <div class="flex flex-col gap-2">
                        <x-device-auth::button x-on:click="phase = 'request'">Request access for this device</x-device-auth::button>
                        <x-device-auth::button variant="secondary" x-on:click="verify()">Try another key</x-device-auth::button>
                    </div>
                </div>
            </template>

            {{-- No key --}}
            <template x-if="!problem && phase === 'not_found'">
                <div class="grid gap-5">
                    <p>Either this device doesn’t have a key for this app yet, or the device prompt was closed.</p>
                    <div class="flex flex-col gap-2">
                        <x-device-auth::button x-on:click="phase = 'request'">Request access for this device</x-device-auth::button>
                        <x-device-auth::button variant="secondary" x-on:click="verify()">Try again</x-device-auth::button>
                    </div>
                </div>
            </template>

            <x-slot:footer>
                @if ($user)
                    <span>Signed in as {{ $user['email'] ?? $user['name'] }}.</span>
                @endif
                @if ($urls['logout'])
                    <form method="POST" action="{{ $urls['logout'] }}" class="inline">
                        @csrf
                        <button type="submit" class="underline underline-offset-4 hover:text-neutral-900 dark:hover:text-neutral-100">Sign out</button>
                    </form>
                @endif
            </x-slot:footer>
        </x-device-auth::shell>
    </div>
</div>

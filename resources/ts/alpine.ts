/**
 * Alpine data for the Livewire screens. Each object drives one WebAuthn
 * flow against the core JSON endpoints, exactly like the React pages.
 */
import { collectClientMeta, suggestDeviceName } from './client-meta';
import { formatDateTime } from './format';
import { DeviceAuthRequestError, sendJson } from './http';
import {
    createDeviceKey,
    keyErrorKind,
    signWithDeviceKey,
    supportProblem,
    type CreationOptionsJSON,
    type RequestOptionsJSON,
    type SupportProblem,
} from './webauthn';

export type DeviceStatus = 'pending' | 'active' | 'rejected' | 'revoked';

export interface DevicePublicSummary {
    reference: string;
    name: string;
    status: DeviceStatus;
    requested_at: string | null;
    review_note: string | null;
}

interface DeviceAnswer {
    status: string;
    redirect?: string | null;
    device?: DevicePublicSummary;
    contact?: string | null;
    message?: string;
}

export type VerifyPhase =
    | 'idle'
    | 'verifying'
    | 'not_found'
    | 'request'
    | 'requesting'
    | 'pending'
    | 'rejected'
    | 'revoked'
    | 'mismatch';

export type MarkState = 'idle' | 'waiting' | 'pending' | 'approved' | 'blocked' | 'unknown' | 'problem';

const VERIFY_MARKS: Record<VerifyPhase, MarkState> = {
    idle: 'idle',
    verifying: 'waiting',
    not_found: 'unknown',
    request: 'idle',
    requesting: 'waiting',
    pending: 'pending',
    rejected: 'blocked',
    revoked: 'blocked',
    mismatch: 'problem',
};

const VERIFY_TITLES: Record<VerifyPhase, string> = {
    idle: 'Verify this device',
    verifying: 'Verify this device',
    not_found: 'No approved key found',
    request: 'Request access for this device',
    requesting: 'Request access for this device',
    pending: 'Waiting for approval',
    rejected: 'Request rejected',
    revoked: 'Access removed',
    mismatch: 'This key belongs to another device',
};

export interface VerifyConfig {
    contact: string | null;
    urls: {
        verifyOptions: string;
        verify: string;
        requestOptions: string;
        request: string;
    };
}

export interface ConfirmConfig {
    urls: { options: string; confirm: string };
}

export interface ConfirmModalConfig {
    urls: { options: string | null; confirm: string | null };
}

interface LivewireGlobal {
    dispatch(name: string, params?: Record<string, unknown>): void;
    navigate?(url: string): void;
}

interface AlpineGlobal {
    data(name: string, callback: (config: never) => object): void;
}

declare global {
    interface Window {
        Alpine?: AlpineGlobal;
        Livewire?: LivewireGlobal;
        DeviceAuth?: { registered: boolean };
    }
}

function visit(url: string): void {
    window.location.assign(url);
}

function confirmError(error: unknown): string {
    if (keyErrorKind(error) === 'cancelled') {
        return 'The device prompt was closed. Choose Confirm device to try again.';
    }

    if (error instanceof DeviceAuthRequestError) {
        return error.message;
    }

    return 'This device couldn’t be confirmed. Try again.';
}

export function deviceAuthVerify(config: VerifyConfig) {
    return {
        problem: supportProblem() as SupportProblem,
        phase: 'idle' as VerifyPhase,
        device: null as DevicePublicSummary | null,
        contact: config.contact,
        error: null as string | null,
        name: suggestDeviceName(),
        note: '',
        fieldErrors: {} as Record<string, string>,

        get busy(): boolean {
            return this.phase === 'verifying' || this.phase === 'requesting';
        },

        /** Which StatusMark the shell shows. */
        get mark(): MarkState {
            if (this.problem) {
                return 'problem';
            }

            return VERIFY_MARKS[this.phase];
        },

        get title(): string {
            if (this.problem) {
                return this.problem === 'insecure'
                    ? 'This page needs a secure connection'
                    : 'This browser can’t use device keys';
            }

            return VERIFY_TITLES[this.phase];
        },

        formatDateTime,

        handleAnswer(answer: DeviceAnswer): void {
            if (answer.status === 'active' && answer.redirect) {
                visit(answer.redirect);

                return;
            }

            if (answer.device) {
                this.device = answer.device;
            }

            if (answer.contact !== undefined) {
                this.contact = answer.contact;
            }

            switch (answer.status) {
                case 'pending':
                case 'rejected':
                case 'revoked':
                case 'mismatch':
                    this.phase = answer.status;
                    break;
                case 'not_registered':
                    this.phase = 'not_found';
                    break;
                default:
                    this.error = answer.message ?? 'Something went wrong. Try again.';
                    this.phase = 'idle';
            }
        },

        async verify(): Promise<void> {
            this.phase = 'verifying';
            this.error = null;

            try {
                const options = await sendJson<RequestOptionsJSON>('POST', config.urls.verifyOptions);
                const signed = await signWithDeviceKey(options);

                this.handleAnswer(await sendJson<DeviceAnswer>('POST', config.urls.verify, signed));
            } catch (e) {
                if (e instanceof DeviceAuthRequestError && e.reason) {
                    this.handleAnswer({
                        ...(e.data as Partial<DeviceAnswer>),
                        status: e.reason,
                        message: e.message,
                    });
                } else if (keyErrorKind(e) === 'cancelled') {
                    // No key for this site on this device, or the prompt was closed.
                    this.phase = 'not_found';
                } else {
                    this.error = e instanceof Error ? e.message : 'This device couldn’t be verified.';
                    this.phase = 'idle';
                }
            }
        },

        async requestAccess(): Promise<void> {
            this.error = null;
            this.fieldErrors = {};

            if (!this.name.trim()) {
                this.fieldErrors = { name: 'Give this device a name, like “Reception PC”.' };

                return;
            }

            this.phase = 'requesting';

            try {
                const options = await sendJson<CreationOptionsJSON>('POST', config.urls.requestOptions, {
                    name: this.name,
                });
                const credential = await createDeviceKey(options);
                const client = await collectClientMeta();

                this.handleAnswer(
                    await sendJson<DeviceAnswer>('POST', config.urls.request, {
                        name: this.name,
                        note: this.note || null,
                        client,
                        credential,
                    }),
                );
            } catch (err) {
                this.phase = 'request';

                if (err instanceof DeviceAuthRequestError) {
                    const errors = (err.data.errors ?? {}) as Record<string, string[]>;

                    this.fieldErrors = Object.fromEntries(
                        Object.entries(errors).map(([key, messages]) => [key, messages[0] ?? '']),
                    );
                    this.error = Object.keys(errors).length ? null : err.message;
                } else if (keyErrorKind(err) === 'cancelled') {
                    this.error =
                        'The device prompt was closed, so no key was created. Send the request again when you’re ready.';
                } else if (keyErrorKind(err) === 'exists') {
                    this.error = 'This device already has a key for this app. Go back and choose Verify device.';
                } else {
                    this.error =
                        'This device couldn’t create a key. Check that it has a screen lock (PIN, fingerprint or face) set up.';
                }
            }
        },
    };
}

export function deviceAuthConfirm(config: ConfirmConfig) {
    return {
        busy: false,
        error: null as string | null,

        async confirm(): Promise<void> {
            this.busy = true;
            this.error = null;

            try {
                const options = await sendJson<RequestOptionsJSON>('POST', config.urls.options);
                const signed = await signWithDeviceKey(options);
                const result = await sendJson<{ redirect: string | null }>('POST', config.urls.confirm, {
                    ...signed,
                    page: true,
                });

                visit(result.redirect ?? '/');
            } catch (e) {
                this.busy = false;
                this.error = confirmError(e);
            }
        },
    };
}

/**
 * The confirm dialog for component actions. Opens on the
 * "device-auth:confirm-required" browser event (dispatched by the
 * ConfirmsDevice trait) and answers with the "device-auth:confirmed"
 * Livewire event, which replays the action that asked.
 */
export function deviceAuthConfirmModal(config: ConfirmModalConfig) {
    return {
        open: false,
        busy: false,
        error: null as string | null,

        init(): void {
            window.addEventListener('device-auth:confirm-required', () => {
                this.error = null;
                this.busy = false;
                this.open = true;
            });
        },

        close(): void {
            if (!this.busy) {
                this.open = false;
            }
        },

        async confirm(): Promise<void> {
            if (!config.urls.options || !config.urls.confirm) {
                return;
            }

            this.busy = true;
            this.error = null;

            try {
                const options = await sendJson<RequestOptionsJSON>('POST', config.urls.options);
                const signed = await signWithDeviceKey(options);

                await sendJson('POST', config.urls.confirm, signed);

                this.busy = false;
                this.open = false;
                window.Livewire?.dispatch('device-auth:confirmed');
            } catch (e) {
                this.busy = false;
                this.error = confirmError(e);
            }
        },
    };
}

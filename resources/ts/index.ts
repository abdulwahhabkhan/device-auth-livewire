import { deviceAuthConfirm, deviceAuthConfirmModal, deviceAuthVerify } from './alpine';

function register(): void {
    const alpine = window.Alpine;

    if (!alpine || window.DeviceAuth?.registered) {
        return;
    }

    alpine.data('deviceAuthVerify', deviceAuthVerify);
    alpine.data('deviceAuthConfirm', deviceAuthConfirm);
    alpine.data('deviceAuthConfirmModal', deviceAuthConfirmModal);
    window.DeviceAuth = { registered: true };
}

// Livewire boots Alpine after this script: register on alpine:init.
// When loaded later (wire:navigate), Alpine already exists: register now.
document.addEventListener('alpine:init', register);
register();

import { ref, computed, nextTick, type ComponentPublicInstance } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import type { User } from '@/types';

export function useTwoFactorAuthentication() {
    const page = usePage();
    const user = computed(() => page.props.auth.user as User & { two_factor_enabled: boolean });

    const enabling = ref(false);
    const confirming = ref(false);
    const qrCode = ref<string | null>(null);
    const recoveryCodes = ref<string[]>([]);
    const showingRecoveryCodes = ref(false);
    const confirmationForm = useForm({ code: '' });
    const twoFactorEnabled = computed(() => user.value && user.value.two_factor_enabled);

    const confirmingPassword = ref(false);
    const passwordForConfirmationInput = ref<ComponentPublicInstance | null>(null);
    const confirmationPassword = ref('');
    const confirmationPasswordProcessing = ref(false);
    const confirmationPasswordError = ref('');
    const actionToRetryAfterConfirmation = ref<(() => Promise<void>) | null>(null);

    const ensurePasswordIsConfirmed = async (action: () => Promise<void>) => {
        try {
            await action();
        } catch (error: any) {
            if (error.response && error.response.status === 423) {
                actionToRetryAfterConfirmation.value = action;
                confirmingPassword.value = true;
                nextTick(() => {
                    if (passwordForConfirmationInput.value) {
                        (passwordForConfirmationInput.value.$el as HTMLInputElement).focus();
                    }
                });
            } else {
                console.error('Wystąpił nieoczekiwany błąd:', error);
            }
        }
    };

    const confirmPasswordAndRetryAction = async () => {
        confirmationPasswordProcessing.value = true;
        confirmationPasswordError.value = '';
        try {
            await axios.post(route('password.confirm'), { password: confirmationPassword.value });
            confirmationPasswordProcessing.value = false;
            confirmingPassword.value = false;
            confirmationPassword.value = '';
            if (actionToRetryAfterConfirmation.value) {
                await actionToRetryAfterConfirmation.value();
                actionToRetryAfterConfirmation.value = null;
            }
        } catch (error: any) {
            confirmationPasswordProcessing.value = false;
            confirmationPasswordError.value = error.response?.data.errors?.password?.[0] || 'Wystąpił błąd.';
            if (passwordForConfirmationInput.value) {
                (passwordForConfirmationInput.value.$el as HTMLInputElement).focus();
            }
        }
    };

    const showQrCode = async () => {
        const response = await axios.get(route('two-factor.qr-code'));
        qrCode.value = response.data.svg;
    };

    const showRecoveryCodes = async () => {
        const response = await axios.get(route('two-factor.recovery-codes'));
        recoveryCodes.value = response.data;
    };

    const enableTwoFactorAuthentication = () => ensurePasswordIsConfirmed(async () => {
        await axios.post(route('two-factor.enable'));
        await Promise.all([showQrCode(), showRecoveryCodes()]);
        enabling.value = true;
        confirming.value = true;
    });

    const displayRecoveryCodes = () => ensurePasswordIsConfirmed(async () => {
        await showRecoveryCodes();
        showingRecoveryCodes.value = true;
    });
    
    const regenerateRecoveryCodes = () => ensurePasswordIsConfirmed(async () => {
        await axios.post(route('two-factor.recovery-codes'));
        await showRecoveryCodes();
    });

    const disableTwoFactorAuthentication = () => ensurePasswordIsConfirmed(async () => {
        await router.delete(route('two-factor.disable'), {
            preserveScroll: true,
            onSuccess: () => {
                showingRecoveryCodes.value = false;
                recoveryCodes.value = [];
                router.reload();
            },
        });
    });

    const confirmTwoFactorAuthentication = () => {
        confirmationForm.post(route('two-factor.confirm'), {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({
                    onSuccess: () => {
                        enabling.value = false;
                        confirming.value = false;
                        showingRecoveryCodes.value = false;
                        qrCode.value = null;
                        recoveryCodes.value = [];
                    },
                });
            },
        });
    };

    return {
        user,
        twoFactorEnabled,
        enabling,
        confirming,
        qrCode,
        recoveryCodes,
        showingRecoveryCodes,
        confirmationForm,
        confirmingPassword,
        passwordForConfirmationInput,
        confirmationPassword,
        confirmationPasswordProcessing,
        confirmationPasswordError,
        confirmPasswordAndRetryAction,
        displayRecoveryCodes,
        enableTwoFactorAuthentication,
        regenerateRecoveryCodes,
        disableTwoFactorAuthentication,
        confirmTwoFactorAuthentication,
    };
}
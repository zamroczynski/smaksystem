<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import { useTwoFactorAuthentication } from '@/composables/useTwoFactorAuthentication';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import InputError from '@/components/InputError.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Ustawienia hasła',
        href: '/settings/password',
    },
];

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors: any) => {
            if (errors.password) {
                form.reset('password', 'password_confirmation');
                if (passwordInput.value instanceof HTMLInputElement) {
                    passwordInput.value.focus();
                }
            }

            if (errors.current_password) {
                form.reset('current_password');
                if (currentPasswordInput.value instanceof HTMLInputElement) {
                    currentPasswordInput.value.focus();
                }
            }
        },
    });
};

const {
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
} = useTwoFactorAuthentication();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Ustawienia hasła" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Zaktualizuj hasło"
                    description="Upewnij się, że Twoje hasło jest wystarczająco długie i bezpieczne" />

                <form @submit.prevent="updatePassword" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="current_password">Obecne hasło</Label>
                        <Input id="current_password" ref="currentPasswordInput" v-model="form.current_password"
                            type="password" class="mt-1 block w-full" autocomplete="current-password"
                            placeholder="Obecne hasło" />
                        <InputError :message="form.errors.current_password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Nowe hasło</Label>
                        <Input id="password" ref="passwordInput" v-model="form.password" type="password"
                            class="mt-1 block w-full" autocomplete="new-password" placeholder="Nowe hasło" />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">Potwierdź nowe hasło</Label>
                        <Input id="password_confirmation" v-model="form.password_confirmation" type="password"
                            class="mt-1 block w-full" autocomplete="new-password" placeholder="Potwierdź nowe hasło" />
                        <InputError :message="form.errors.password_confirmation" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Zapisz</Button>

                        <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Zapisano.</p>
                        </Transition>
                    </div>
                </form>
            </div>

            <Separator class="my-8" />

            <div class="space-y-6">
                <HeadingSmall title="Uwierzytelnianie Dwuskładnikowe"
                    description="Dodaj dodatkową warstwę bezpieczeństwa do swojego konta." />

                <div v-if="twoFactorEnabled && !confirming">
                    <p class="text-sm text-green-600 font-medium">Masz włączone uwierzytelnianie dwuskładnikowe.</p>
                </div>
                <div v-else-if="twoFactorEnabled && confirming">
                    <p class="text-sm font-medium">Zakończ włączanie uwierzytelniania dwuskładnikowego.</p>
                </div>
                <div v-else>
                    <p class="text-sm text-muted-foreground">Nie masz włączonego uwierzytelniania dwuskładnikowego.</p>
                </div>

                <div v-if="!user.two_factor_enabled">
                    <Button @click="enableTwoFactorAuthentication">Włącz 2FA</Button>
                </div>

                <div v-else>
                    <div class="flex items-center gap-4">
                        <Button v-if="!showingRecoveryCodes" @click="displayRecoveryCodes">
                            Pokaż kody odzyskiwania
                        </Button>
                        <Button v-if="showingRecoveryCodes" @click="regenerateRecoveryCodes">
                            Wygeneruj nowe kody
                        </Button>
                        <Button @click="disableTwoFactorAuthentication" variant="destructive">Wyłącz 2FA</Button>
                    </div>
                </div>

                <div v-if="enabling" class="space-y-4">
                    <p class="text-sm text-muted-foreground">
                        Zeskanuj poniższy kod QR za pomocą aplikacji uwierzytelniającej na telefonie (np. Google
                        Authenticator) lub wprowadź klucz
                        konfiguracyjny.
                    </p>

                    <div class="p-2 inline-block bg-white" v-html="qrCode"></div>

                    <div v-if="recoveryCodes.length > 0" class="space-y-2">
                        <p class="text-sm text-muted-foreground">
                            Zachowaj te kody odzyskiwania w bezpiecznym miejscu. Mogą one zostać użyte do odzyskania
                            dostępu do Twojego konta, jeśli
                            utracisz urządzenie uwierzytelniające.
                        </p>
                        <div class="grid gap-1 rounded-lg bg-gray-100 dark:bg-gray-900 p-4 font-mono text-sm">
                            <div v-for="code in recoveryCodes" :key="code">{{ code }}</div>
                        </div>
                    </div>
                </div>

                <div v-if="confirming" class="space-y-4">
                    <form @submit.prevent="confirmTwoFactorAuthentication">
                        <div class="grid gap-2 max-w-xs">
                            <Label for="code">Kod weryfikacyjny</Label>
                            <Input id="code" v-model="confirmationForm.code" type="text" inputmode="numeric" autofocus
                                autocomplete="one-time-code" placeholder="Wpisz kod z aplikacji" />
                            <InputError :message="confirmationForm.errors.code" />
                        </div>
                        <Button class="mt-4" :disabled="confirmationForm.processing">Potwierdź</Button>
                    </form>
                </div>

                <div v-if="showingRecoveryCodes && !confirming" class="space-y-4">
                    <p class="text-sm text-muted-foreground">
                        Zachowaj te kody odzyskiwania w bezpiecznym miejscu. Mogą one zostać użyte do odzyskania dostępu
                        do Twojego konta, jeśli
                        utracisz urządzenie uwierzytelniające.
                    </p>
                    <div class="grid gap-1 rounded-lg bg-gray-100 dark:bg-gray-900 p-4 font-mono text-sm">
                        <div v-for="code in recoveryCodes" :key="code">{{ code }}</div>
                    </div>
                </div>
            </div>

            <div v-if="confirmingPassword"
                class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center">
                <div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md">
                    <h3 class="text-lg font-medium">Potwierdź hasło</h3>
                    <p class="text-sm text-muted-foreground mt-1">
                        Ta akcja wymaga ponownego wprowadzenia hasła w celach bezpieczeństwa.
                    </p>

                    <form @submit.prevent="confirmPasswordAndRetryAction" class="mt-4 space-y-4">
                        <div class="grid gap-2">
                            <Label for="password_for_confirmation">Hasło</Label>
                            <Input id="password_for_confirmation" ref="passwordForConfirmationInput"
                                v-model="confirmationPassword" type="password" autocomplete="current-password"
                                placeholder="Wpisz swoje hasło" />
                            <InputError :message="confirmationPasswordError" />
                        </div>
                        <div class="flex justify-end gap-4">
                            <Button variant="ghost" @click="confirmingPassword = false">Anuluj</Button>
                            <Button :disabled="confirmationPasswordProcessing">Potwierdź</Button>
                        </div>
                    </form>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

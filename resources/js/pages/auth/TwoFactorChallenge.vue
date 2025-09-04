<script setup lang="ts">
import { ref, nextTick } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import AuthBase from '@/layouts/AuthLayout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const recoveryMode = ref(false);
const recoveryCodeInput = ref<HTMLInputElement | null>(null);
const codeInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    code: '',
    recovery_code: '',
});

const toggleRecoveryMode = async () => {
    recoveryMode.value = !recoveryMode.value;

    await nextTick();

    if (recoveryMode.value) {
        recoveryCodeInput.value?.focus();
        form.reset('code');
    } else {
        codeInput.value?.focus();
        form.reset('recovery_code');
    }
};

const submit = () => {
    const routeName = recoveryMode.value ? 'two-factor.recovery' : 'two-factor.login';
    form.post(route(routeName));
};
</script>

<template>
    <AuthBase title="Weryfikacja Dwuskładnikowa" description="Potwierdź dostęp do swojego konta">
        <Head title="Weryfikacja Dwuskładnikowa" />

        <div class="mb-4 text-sm text-muted-foreground">
            <template v-if="!recoveryMode">
                Wprowadź kod weryfikacyjny wygenerowany przez Twoją aplikację uwierzytelniającą.
            </template>
            <template v-else>
                Wprowadź jeden ze swoich kodów odzyskiwania.
            </template>
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div v-if="!recoveryMode" class="grid gap-2">
                    <Label for="code">Kod Weryfikacyjny</Label>
                    <Input
                        id="code"
                        ref="codeInput"
                        v-model="form.code"
                        type="text"
                        inputmode="numeric"
                        autofocus
                        autocomplete="one-time-code"
                        placeholder="_ _ _ _ _ _"
                    />
                    <InputError :message="form.errors.code" />
                </div>

                <div v-else class="grid gap-2">
                    <Label for="recovery_code">Kod Odzyskiwania</Label>
                    <Input
                        id="recovery_code"
                        ref="recoveryCodeInput"
                        v-model="form.recovery_code"
                        type="text"
                        autocomplete="one-time-code"
                        placeholder="np. abc-123"
                    />
                    <InputError :message="form.errors.recovery_code" />
                </div>

                <div class="flex items-center justify-end">
                    <Button type="button" variant="link" class="p-0 h-auto" @click.prevent="toggleRecoveryMode">
                        <template v-if="!recoveryMode">
                            Użyj kodu odzyskiwania
                        </template>
                        <template v-else>
                            Użyj kodu z aplikacji
                        </template>
                    </Button>
                </div>

                <Button type="submit" class="mt-4 w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Zaloguj się
                </Button>
            </div>
        </form>
    </AuthBase>
</template>
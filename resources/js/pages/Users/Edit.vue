<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'vue-sonner';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

import { EyeIcon, EyeOffIcon } from 'lucide-vue-next';

const props = defineProps<{
    user: {
        id: number;
        name: string;
        login: string;
        email: string;
        current_role?: string; // Aktualna rola użytkownika
    };
    roles: Array<{ id: number; name: string }>; // Wszystkie dostępne role
    flash?: {
        success?: string;
        error?: string;
    };
}>();

// Formularz dla danych użytkownika (bez hasła)
const userForm = useForm({
    name: props.user.name,
    login: props.user.login,
    email: props.user.email,
    role_name: props.user.current_role || null, // Ustaw bieżącą rolę lub pusty string
});

// Oddzielny formularz dla zmiany hasła
const passwordForm = useForm({
    password: '',
});

const showPassword = ref(false);

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const submitUserForm = () => {
    userForm.put(route('users.update', props.user.id), {
        onSuccess: () => {
            toast.success(props.flash?.success || 'Dane pracownika zostały pomyślnie zaktualizowane.');

            router.reload({ only: ['user'] });
        },
        onError: (errors) => {
            if (Object.keys(errors).length === 0) {
                toast.error(props.flash?.error || 'Wystąpił błąd podczas aktualizacji danych pracownika.');
            } else {
                toast.error('Wystąpiły błędy walidacji. Sprawdź formularz.');
            }
        },
    });
};

const submitPasswordForm = () => {
    passwordForm.put(route('users.update', props.user.id), {
        onSuccess: () => {
            toast.success(props.flash?.success || 'Hasło pracownika zostało pomyślnie zmienione.');
            passwordForm.reset(); // Zresetuj tylko formularz hasła
        },
        onError: (errors) => {
            if (Object.keys(errors).length === 0) {
                toast.error(props.flash?.error || 'Wystąpił błąd podczas zmiany hasła pracownika.');
            } else {
                toast.error('Wystąpiły błędy walidacji. Sprawdź formularz hasła.');
            }
        },
    });
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Panel nawigacyjny',
        href: '/dashboard',
    },
    {
        title: 'Zarządzanie Użytkownikami',
        href: '/users',
    },
    {
        title: `Edycja: ${props.user.name}`,
        href: `/users/${props.user.id}/edit`,
    },
];
</script>

<template>

    <Head :title="`Edycja: ${props.user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Edytuj Pracownika: {{
                    props.user.name }}</h3>

                <form @submit.prevent="submitUserForm" class="space-y-4">
                    <div>
                        <Label for="name">Nazwa <span class="text-red-500">*</span></Label>
                        <Input id="name" type="text" v-model="userForm.name" required />
                        <p v-if="userForm.errors.name" class="text-sm text-red-500 mt-1">{{ userForm.errors.name }}</p>
                    </div>

                    <div>
                        <Label for="login">Login <span class="text-red-500">*</span></Label>
                        <Input id="login" type="text" v-model="userForm.login" required />
                        <p v-if="userForm.errors.login" class="text-sm text-red-500 mt-1">{{ userForm.errors.login }}
                        </p>
                    </div>

                    <div>
                        <Label for="email">Email</Label>
                        <Input id="email" type="email" v-model="userForm.email" />
                        <p v-if="userForm.errors.email" class="text-sm text-red-500 mt-1">{{ userForm.errors.email }}
                        </p>
                    </div>

                    <div>
                        <Label for="role_name">Rola</Label>
                        <Select v-model="userForm.role_name">
                            <SelectTrigger class="w-full">
                                <SelectValue :placeholder="userForm.role_name || 'Wybierz rolę'" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="role in props.roles" :key="role.id" :value="role.name">
                                    {{ role.name }}
                                </SelectItem>
                                <SelectItem value="null">(Brak roli)</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="userForm.errors.role_name" class="text-sm text-red-500 mt-1">{{
                            userForm.errors.role_name }}</p>
                    </div>

                    <div class="flex justify-end">
                        <Button type="submit" :disabled="userForm.processing">Zapisz zmiany</Button>
                    </div>
                </form>

                <div class="my-8">
                    <hr class="border-t border-gray-200 dark:border-gray-700" />
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Zmień Hasło</CardTitle>
                        <CardDescription>Użyj tego formularza, aby zmienić hasło użytkownika.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submitPasswordForm" class="space-y-4">
                            <div>
                                <Label for="password">Nowe Hasło</Label>
                                <div class="relative">
                                    <Input id="password" :type="showPassword ? 'text' : 'password'"
                                        v-model="passwordForm.password" class="pr-10" />
                                    <Button type="button" variant="ghost" size="sm"
                                        class="absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent"
                                        @click="togglePasswordVisibility">
                                        <EyeIcon v-if="!showPassword" class="h-4 w-4 text-gray-500" />
                                        <EyeOffIcon v-else class="h-4 w-4 text-gray-500" />
                                    </Button>
                                </div>
                                <p v-if="passwordForm.errors.password" class="text-sm text-red-500 mt-1">{{
                                    passwordForm.errors.password }}</p>
                            </div>
                            <div class="flex justify-end">
                                <Button type="submit" :disabled="passwordForm.processing">Zmień Hasło</Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
<script setup lang="ts">
import { router } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input'; 
import { Label } from '@/components/ui/label'; 
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'; 
import { toast } from 'vue-sonner';

const props = defineProps<{
    roles: Array<{ id: number; name: string }>; 
    flash?: {
        success?: string;
        error?: string;
    };
}>();

const form = useForm({
    name: '',
    login: '',
    email: '',
    password: '',
    role_name: '', 
});

const submit = () => {
    form.post(route('users.store'), {
        onSuccess: () => {
            toast.success(props.flash?.success || 'Użytkownik został pomyślnie dodany.');
            form.reset();
 
            router.reload({ only: ['users'] }); // Odświeża tylko prop 'users' na aktualnej stronie
        },
        onError: (errors) => {
            if (Object.keys(errors).length === 0) {
                 toast.error(props.flash?.error || 'Wystąpił błąd podczas dodawania użytkownika.');
            } else {
                toast.error('Wystąpiły błędy walidacji. Sprawdź formularz.');
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
        title: 'Zarządzanie pracownikami',
        href: '/users',
    },
    {
        title: 'Dodaj pracownika',
        href: '/users/create',
    },
];
</script>

<template>
    <Head title="Dodaj Pracownika" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Dodaj Pracownika</h3>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <Label for="name">Nazwa <span class="text-red-500">*</span></Label>
                        <Input id="name" type="text" v-model="form.name" required />
                        <p v-if="form.errors.name" class="text-sm text-red-500 mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <Label for="login">Login <span class="text-red-500">*</span></Label>
                        <Input id="login" type="text" v-model="form.login" required />
                        <p v-if="form.errors.login" class="text-sm text-red-500 mt-1">{{ form.errors.login }}</p>
                    </div>

                    <div>
                        <Label for="email">Email</Label>
                        <Input id="email" type="email" v-model="form.email" />
                        <p v-if="form.errors.email" class="text-sm text-red-500 mt-1">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <Label for="password">Hasło <span class="text-red-500">*</span></Label>
                        <Input id="password" type="password" v-model="form.password" required />
                        <p v-if="form.errors.password" class="text-sm text-red-500 mt-1">{{ form.errors.password }}</p>
                    </div>

                    <div>
                        <Label for="role_name">Rola</Label>
                        <Select v-model="form.role_name">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Wybierz rolę" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="role in props.roles" :key="role.id" :value="role.name">
                                    {{ role.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.role_name" class="text-sm text-red-500 mt-1">{{ form.errors.role_name }}</p>
                    </div>

                    <div class="flex justify-end">
                        <Button type="submit" :disabled="form.processing">Dodaj pracownika</Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Permission } from '@/types/models';
import { Head, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';

const props = defineProps<{
    permissions: Permission[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Panel nawigacyjny',
        href: '/dashboard',
    },
    {
        title: 'Zarządzanie Rolami',
        href: '/roles',
    },
    {
        title: 'Dodaj Rolę',
        href: '/roles/create',
    },
];

const form = useForm({
    name: '',
    permissions: [] as string[],
});

const hasPermissionsError = () => {
    // Sprawdza, czy którykolwiek klucz błędu zaczyna się od 'permissions.'
    return Object.keys(form.errors).some(key => key.startsWith('permissions.'));
};

const getFirstPermissionsError = () => {
    for (const key in form.errors) {
        if (key.startsWith('permissions.')) {
            return (form.errors as any)[key];
        }
    }
    // Jeśli jest ogólny błąd 'permissions', zwróć go
    if (form.errors.permissions) {
        return form.errors.permissions;
    }
    return null;
};

const handlePermissionChange = (permissionName: string, isChecked: boolean | 'indeterminate') => {
    // Ignoruj stan 'indeterminate', interesuje nas tylko true/false
    if (isChecked === 'indeterminate') {
        return;
    }

    if (isChecked) {
        // Dodaj nazwę uprawnienia, jeśli checkbox jest zaznaczony i jeszcze jej nie ma
        if (!form.permissions.includes(permissionName)) {
            form.permissions.push(permissionName);
        }
    } else {
        // Usuń nazwę uprawnienia, jeśli checkbox jest odznaczony
        form.permissions = form.permissions.filter(name => name !== permissionName);
    }
};

const submit = () => {
    form.post(route('roles.store'), {
        onSuccess: () => {
            toast.success('Rola została pomyślnie dodana.');
            form.reset();
        },
        onError: (errors) => {
            if (Object.keys(errors).length === 0) {
                toast.error('Wystąpił nieoczekiwany błąd podczas dodawania roli.');
            } else {
                toast.error('Wystąpiły błędy walidacji. Sprawdź formularz.');
            }
        },
    });
};
</script>

<template>

    <Head title="Dodaj Rolę" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <Card>
                <CardHeader>
                    <CardTitle>Dodaj Nową Rolę</CardTitle>
                    <CardDescription>Wprowadź nazwę nowej roli do systemu.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <Label for="name">Nazwa Roli <span class="text-red-500">*</span></Label>
                            <Input id="name" type="text" v-model="form.name" required />
                            <p v-if="form.errors.name" class="text-sm text-red-500 mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <Label>Przypisz Uprawnienia:</Label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-2">
                                <div v-for="permission in permissions" :key="permission.id"
                                    class="flex items-center space-x-2">
                                    <Checkbox
                    :id="`permission-${permission.id}`"
                    :value="permission.name"
                    :model-value="form.permissions.includes(permission.name)"
                    @update:model-value="(isChecked: boolean | 'indeterminate') => handlePermissionChange(permission.name, isChecked)"
                />
                                    <label :for="`permission-${permission.id}`"
                                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                        {{ permission.name }}
                                    </label>
                                </div>
                            </div>
                            <p v-if="hasPermissionsError()" class="text-sm text-red-500 mt-1">{{
                                getFirstPermissionsError() }}</p>
                        </div>

                        <div class="flex justify-end">
                            <Button type="submit" :disabled="form.processing">Dodaj Rolę</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
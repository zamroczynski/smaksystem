<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { type Permission } from '@/types/models';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';

const props = defineProps<{
    role: { id: number; name: string; };
    allPermissions: Permission[];
    rolePermissions: string[];
    breadcrumbs: BreadcrumbItem[];
}>();

const form = useForm({
    name: props.role.name,
    permissions: props.rolePermissions,
});

const hasPermissionsError = () => {
    return Object.keys(form.errors).some(key => key.startsWith('permissions.'));
};

const getFirstPermissionsError = () => {
    for (const key in form.errors) {
        if (key.startsWith('permissions.')) {
            return (form.errors as any)[key];
        }
    }
    if (form.errors.permissions) {
        return form.errors.permissions;
    }
    return null;
};

const handlePermissionChange = (permissionName: string, isChecked: boolean | 'indeterminate') => {
    if (isChecked === 'indeterminate') {
        return;
    }

    if (isChecked) {
        if (!form.permissions.includes(permissionName)) {
            form.permissions.push(permissionName);
        }
    } else {
        form.permissions = form.permissions.filter(name => name !== permissionName);
    }
};

const submit = () => {
    form.put(route('roles.update', props.role.id), {
        onSuccess: () => {
            toast.success('Rola została pomyślnie zaktualizowana.');
        },
        onError: (errors) => {
            if (Object.keys(errors).length === 0) {
                 toast.error('Wystąpił nieoczekiwany błąd podczas aktualizacji roli.');
            } else {
                toast.error('Wystąpiły błędy walidacji. Sprawdź formularz.');
            }
        },
    });
};
</script>

<template>
    <Head :title="`Edytuj Rolę: ${role.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <Card>
                <CardHeader>
                    <CardTitle>Edytuj Rolę: {{ role.name }}</CardTitle>
                    <CardDescription>Zmień nazwę roli i przypisane do niej uprawnienia.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="name">Nazwa Roli <span class="text-red-500">*</span></Label>
                            <Input
                                id="name"
                                type="text"
                                v-model="form.name"
                                required
                            />
                            <p v-if="form.errors.name" class="text-sm text-red-500 mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <Label>Przypisz Uprawnienia:</Label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-2">
                                <div v-for="permission in allPermissions" :key="permission.id" class="flex items-center space-x-2">
                                    <Checkbox
                                        :id="`permission-${permission.id}`"
                                        :value="permission.name"
                                        :model-value="form.permissions.includes(permission.name)"
                                        @update:model-value="(value: boolean | 'indeterminate') => handlePermissionChange(permission.name, value)"
                                    />
                                    <label
                                        :for="`permission-${permission.id}`"
                                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        {{ permission.name }}
                                    </label>
                                </div>
                            </div>
                            <p v-if="hasPermissionsError()" class="text-sm text-red-500 mt-1">{{ getFirstPermissionsError() }}</p>
                        </div>

                        <div class="flex justify-end">
                            <Button type="submit" :disabled="form.processing">Zapisz Zmiany</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
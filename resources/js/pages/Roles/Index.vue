<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { toast } from 'vue-sonner';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import Pagination from '@/components/Pagination.vue';

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { ref, watch } from 'vue';

const props = defineProps<{
    roles: {
        data: Array<{
            id: number;
            name: string;
            deleted_at: string | null;
            is_assigned_to_users?: boolean;
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        from: number;
        last_page: number;
        per_page: number;
        to: number;
        total: number;
    };
    flash?: {
        success?: string;
        error?: string;
    };
    show_disabled: boolean;
    breadcrumbs: BreadcrumbItem[]
}>();

const form = useForm({});

const restoreRole = (roleId: number) => {
    router.post(route('roles.restore', roleId), {}, {
        onSuccess: () => {
            toast.success('Rola została pomyślnie przywrócona.');
            router.get(route('roles.index', { show_disabled: showDisabledRoles.value }), {}, {
                preserveState: true,
                preserveScroll: true,
                only: ['roles', 'show_disabled'],
            });
        },
        onError: () => {
            toast.error('Wystąpił błąd podczas przywracania roli.');
        },
    });
};

const isAlertDialogOpen = ref(false);
const roleToDeleteId = ref<number | null>(null);
const roleToDeleteName = ref<string>('');
const isRoleAssignedToUsers = ref<boolean>(false);

const showDisabledRoles = ref(props.show_disabled);

watch(showDisabledRoles, (newValue) => {
    console.log('showDisabledRoles changed:', newValue);
    router.get(route('roles.index', { show_disabled: newValue }), {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['roles', 'show_disabled'],
    });
});

const confirmDelete = (roleId: number, roleName: string, isAssigned: boolean) => {
    roleToDeleteId.value = roleId;
    roleToDeleteName.value = roleName;
    isRoleAssignedToUsers.value = isAssigned;

    isAlertDialogOpen.value = true;
};

const deleteRoleConfirmed = () => {
    if (roleToDeleteId.value !== null) {
        form.delete(route('roles.destroy', roleToDeleteId.value), {
            onSuccess: () => {
                if (props.flash?.error) {
                    toast.error(props.flash.error);
                } else if (props.flash?.success) {
                    toast.success(props.flash.success);
                } else {
                    toast.success('Rola została pomyślnie wyłączona.');
                }
                isAlertDialogOpen.value = false;
                roleToDeleteId.value = null;
                roleToDeleteName.value = '';
                router.get(route('roles.index', { show_disabled: showDisabledRoles.value }), {}, {
                    preserveState: true,
                    preserveScroll: true,
                    only: ['roles', 'show_disabled'],
                });
            },
            onError: () => {
                toast.error(props.flash?.error || 'Wystąpił błąd podczas wyłączania roli.');
                isAlertDialogOpen.value = false;
                roleToDeleteId.value = null;
                roleToDeleteName.value = '';
            },
        });
    }
};

</script>

<template>

    <Head title="Zarządzanie Rolami" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Role</h3>
                    <div class="flex items-center space-x-2">
                        <Switch id="show-disabled-roles" :model-value="showDisabledRoles"
                            @update:model-value="showDisabledRoles = $event" />
                        <Label for="show-disabled-roles">Pokaż tylko wyłączone role</Label>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[100px]">ID</TableHead>
                                <TableHead>Nazwa</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Akcje</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="role in props.roles.data" :key="role.id">
                                <TableCell class="font-medium">{{ role.id }}</TableCell>
                                <TableCell>{{ role.name }}</TableCell>
                                <TableCell>
                                    <span v-if="role.deleted_at === null"
                                        class="text-green-600 font-medium">Aktywna</span>
                                    <span v-else class="text-red-600 font-medium">Wyłączona</span>
                                </TableCell>
                                <TableCell class="text-right flex justify-end space-x-2">
                                    <Button as-child variant="outline" size="sm" v-if="role.deleted_at === null">
                                        <Link :href="route('roles.edit', role.id)">Edytuj</Link>
                                    </Button>
                                    <Button v-if="role.deleted_at !== null" variant="outline" size="sm"
                                        @click="restoreRole(role.id)">
                                        Przywróć
                                    </Button>
                                    <Button v-if="role.deleted_at === null" variant="destructive" size="sm"
                                        @click="confirmDelete(role.id, role.name, role.is_assigned_to_users || false)"
                                        :disabled="role.name === 'admin'">
                                        Wyłącz
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="props.roles.data.length === 0">
                                <TableCell colspan="4" class="text-center text-gray-500">Brak ról do wyświetlenia.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                 <Pagination :links="props.roles.links" class="mt-6" />
                <div class="mt-6">
                    <Button as-child class="w-full">
                        <Link :href="route('roles.create')">Dodaj Rolę</Link>
                    </Button>
                </div>
            </div>
        </div>

        <AlertDialog :open="isAlertDialogOpen" @update:open="isAlertDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Czy na pewno chcesz wyłączyć rolę?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Ta akcja spowoduje wyłączenie roli "{{ roleToDeleteName }}" i nie będzie ona już dostępna do
                        przypisania.
                        <template v-if="isRoleAssignedToUsers">
                            <br><br>
                            <span class="font-semibold text-red-500">WAŻNE:</span> Rola "{{ roleToDeleteName }}" jest
                            obecnie przypisana do jednego lub więcej pracowników. Po wyłączeniu roli, zostanie ona
                            automatycznie odpięta od wszystkich kont pracowników, które ją posiadają.
                            Pracownicy ci utracą uprawnienia związane z tą rolą.
                        </template>
                        <br><br>
                        Wyłączoną rolę będzie można w przyszłości przywrócić.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="isAlertDialogOpen = false">Anuluj</AlertDialogCancel>
                    <AlertDialogAction @click="deleteRoleConfirmed" class="bg-red-600 text-white hover:bg-red-700">
                        Wyłącz Rolę
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
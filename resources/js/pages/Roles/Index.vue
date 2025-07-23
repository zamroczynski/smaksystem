<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { type Role } from '@/types/models';

import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { toast } from 'vue-sonner';

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
import { ref } from 'vue';

const props = defineProps<{
    roles: Role[];
    flash?: {
        success?: string;
        error?: string;
    };
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
];

const form = useForm({});

const isAlertDialogOpen = ref(false);
const roleToDeleteId = ref<number | null>(null);
const roleToDeleteName = ref<string>('');
const isRoleAssignedToUsers = ref<boolean>(false);

const confirmDelete = (roleId: number, roleName: string) => {
    roleToDeleteId.value = roleId;
    roleToDeleteName.value = roleName;
    if (roleName === 'Kierownik') {
        isRoleAssignedToUsers.value = true;
    } else {
        isRoleAssignedToUsers.value = false;
    }

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
                    toast.success('Rola została pomyślnie usunięta.');
                }
                isAlertDialogOpen.value = false;
                roleToDeleteId.value = null;
                roleToDeleteName.value = '';
            },
            onError: (errors) => {
                toast.error(props.flash?.error || 'Wystąpił błąd podczas usuwania roli.');
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
                </div>

                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[100px]">ID</TableHead>
                                <TableHead>Nazwa</TableHead>
                                <TableHead class="text-right">Akcje</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="role in roles" :key="role.id">
                                <TableCell class="font-medium">{{ role.id }}</TableCell>
                                <TableCell>{{ role.name }}</TableCell>
                                <TableCell class="text-right flex justify-end space-x-2">
                                    <Button as-child variant="outline" size="sm">
                                        <Link :href="route('roles.edit', role.id)">Edytuj</Link>
                                    </Button>
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        @click="confirmDelete(role.id, role.name)"
                                        :disabled="role.name === 'Kierownik'"
                                    >
                                        Wyłącz
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="roles.length === 0">
                                <TableCell colspan="3" class="text-center text-gray-500">Brak ról do wyświetlenia.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
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
                        Ta akcja spowoduje **wyłączenie roli "{{ roleToDeleteName }}"** i nie będzie ona już dostępna do przypisania.
                        <template v-if="isRoleAssignedToUsers">
                            <br><br>
                            <span class="font-semibold text-red-500">WAŻNE:</span> Rola "{{ roleToDeleteName }}" jest obecnie przypisana do jednego lub więcej pracowników. Po wyłączeniu roli, **zostanie ona automatycznie odpięta od wszystkich kont pracowników, które ją posiadają.**
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
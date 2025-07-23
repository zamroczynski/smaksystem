<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';
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
import { ref } from 'vue';



const props = defineProps<{
    users: {
        data: Array<{
            id: number;
            name: string;
            login: string;
            email: string;
            created_at: string; 
            roles: string;       
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
}>();

// Usuwanie:
const form = useForm({});
const isAlertDialogOpen = ref(false);
const userToDeleteId = ref<number | null>(null);
const userToDeleteName = ref<string>('');

const confirmDelete = (userId: number, userName: string) => {
    userToDeleteId.value = userId;
    userToDeleteName.value = userName;
    isAlertDialogOpen.value = true;
};

const deleteUserConfirmed = () => {
    if (userToDeleteId.value !== null) {
        form.delete(route('users.destroy', userToDeleteId.value), {
            onSuccess: () => {
                if (props.flash?.error) {
                    toast.error(props.flash.error);
                } else if (props.flash?.success) {
                    toast.success(props.flash.success);
                } else {
                    toast.success('Operacja zakończona sukcesem.');
                }
                isAlertDialogOpen.value = false; 
                userToDeleteId.value = null; 
                userToDeleteName.value = ''; 
            },
            onError: (errors) => {
                toast.error(props.flash?.error || 'Wystąpił nieoczekiwany błąd podczas wyłączania użytkownika.');
                isAlertDialogOpen.value = false; // Zamknij dialog nawet w przypadku błędu
                userToDeleteId.value = null;
                userToDeleteName.value = '';
            },
        });
    }
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
];

const deleteUser = (userId: number) => {
    if (confirm('Czy na pewno chcesz usunąć tego użytkownika?')) {
        const form = useForm({});
        form.delete(route('users.destroy', userId), {
            onSuccess: () => {
                toast.success(props.flash?.success || 'Użytkownik został pomyślnie usunięty.');
            },
            onError: () => {
                toast.error(props.flash?.error || 'Wystąpił błąd podczas usuwania użytkownika.');
            }
        });
    }
};
</script>

<template>
    <Head title="Zarządzanie Pracownikami" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Lista Pracowników</h3>

                <div class="relative w-full overflow-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[50px]">ID</TableHead>
                                <TableHead>Nazwa</TableHead>
                                <TableHead>Login</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Role</TableHead> <TableHead>Utworzono</TableHead> <TableHead class="text-right">Akcje</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="props.users.data.length">
                                <TableRow v-for="user in props.users.data" :key="user.id">
                                    <TableCell class="font-medium">{{ user.id }}</TableCell>
                                    <TableCell>{{ user.name }}</TableCell>
                                    <TableCell>{{ user.login }}</TableCell>
                                    <TableCell>{{ user.email }}</TableCell>
                                    <TableCell>{{ user.roles }}</TableCell> <TableCell>{{ user.created_at }}</TableCell> <TableCell class="text-right flex items-center justify-end space-x-2">
                                        <Button as-child variant="outline" size="sm">
                                            <Link :href="route('users.edit', user.id)">Edytuj</Link>
                                        </Button>
                                        <Button @click="confirmDelete(user.id, user.name)" variant="destructive" size="sm">
                                            Usuń
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </template>
                            <template v-else>
                                <TableRow>
                                    <TableCell colspan="7" class="h-24 text-center">Brak użytkowników do wyświetlenia.</TableCell>
                                </TableRow>
                            </template>
                        </TableBody>
                    </Table>
                </div>

                <Pagination :links="props.users.links" class="mt-6" />

                <div class="mt-6">
                    <Link :href="route('users.create')">
                        <Button class="w-full">Dodaj pracownika</Button>
                    </Link>
                </div>
            </div>
        </div>
        <AlertDialog :open="isAlertDialogOpen" @update:open="isAlertDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Czy na pewno chcesz wyłączyć pracownika?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Ta akcja spowoduje wyłączenie konta użytkownika **{{ userToDeleteName }}**. Użytkownik nie będzie mógł się zalogować. Konto będzie można przywrócić w przyszłości.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="isAlertDialogOpen = false">Anuluj</AlertDialogCancel>
                    <AlertDialogAction @click="deleteUserConfirmed" class="bg-red-600 text-white hover:bg-red-700">
                        Wyłącz
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
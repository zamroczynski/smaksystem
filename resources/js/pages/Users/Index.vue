<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type User } from '@/types/models';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';
import Pagination from '@/components/Pagination.vue';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';

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
    users: {
        data: Array<{
            id: number;
            name: string;
            login: string;
            email: string;
            created_at: string; 
            roles: string[];
            deleted_at: string | null;       
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
                router.get(route('users.index', { show_disabled: showDisabledUsers.value }), {}, {
                    preserveState: true,
                    preserveScroll: true,
                    only: ['users', 'show_disabled'],
                }); 
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

const showDisabledUsers = ref(props.show_disabled);
watch(showDisabledUsers, (newValue) => {
    router.get(route('users.index', { show_disabled: newValue }), {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['users', 'show_disabled'], // Przeładuj tylko te propsy
    });
});
const restoreUser = (userId: number) => {
    router.post(route('users.restore', userId), {}, {
        onSuccess: () => {
            toast.success('Użytkownik został pomyślnie przywrócony.');
            // Po przywróceniu, przeładuj dane
            router.get(route('users.index', { show_disabled: showDisabledUsers.value }), {}, {
                preserveState: true,
                preserveScroll: true,
                only: ['users', 'show_disabled'],
            });
        },
        onError: () => {
            toast.error('Wystąpił błąd podczas przywracania użytkownika.');
        },
    });
};
</script>

<template>
    <Head title="Zarządzanie Pracownikami" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Lista Pracowników</h3>
                    <div class="flex items-center space-x-2">
                            <Switch
                                id="show-disabled-users"
                                :model-value="showDisabledUsers"
                                @update:model-value="showDisabledUsers = $event"
                            />
                            <Label for="show-disabled-users">Pokaż tylko wyłączonych użytkowników</Label>
                    </div>
                </div>
                <div class="relative w-full overflow-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[100px]">ID</TableHead>
                                <TableHead>Nazwa</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Akcje</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in props.users.data" :key="user.id">
                                <TableCell class="font-medium">{{ user.id }}</TableCell>
                                <TableCell>{{ user.name }}</TableCell>
                                <TableCell>{{ user.email }}</TableCell>
                                <TableCell>
                                    <span v-if="user.roles && user.roles.length > 0">
                                        {{ user.roles.join(', ') }}
                                    </span>
                                    <span v-else class="text-gray-500">Brak</span>
                                </TableCell>
                                <TableCell>
                                    <span v-if="user.deleted_at === null" class="text-green-600 font-medium">Aktywny</span>
                                    <span v-else class="text-red-600 font-medium">Wyłączony</span>
                                </TableCell>
                                <TableCell class="text-right flex justify-end space-x-2">
                                    <Button as-child variant="outline" size="sm" v-if="user.deleted_at === null">
                                        <Link :href="route('users.edit', user.id)">Edytuj</Link>
                                    </Button>
                                    <Button
                                        v-if="user.deleted_at !== null"
                                        variant="outline"
                                        size="sm"
                                        @click="restoreUser(user.id)"
                                    >
                                        Przywróć
                                    </Button>
                                    <Button
                                        v-if="user.deleted_at === null"
                                        variant="destructive"
                                        size="sm"
                                        @click="confirmDelete(user.id, user.name)"
                                        :disabled="user.id === $page.props.auth.user.id || user.roles.includes('admin')" >
                                        Wyłącz
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="props.users.data.length === 0">
                                <TableCell colspan="6" class="text-center text-gray-500">Brak użytkowników do wyświetlenia.</TableCell>
                            </TableRow>
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
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type UserIndexProps } from '@/types';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';
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
import { ref, watch, h, defineAsyncComponent } from 'vue';

import { ColumnDef } from '@tanstack/vue-table';

const DataTable = defineAsyncComponent(() =>
  import('@/components/DataTable.vue')
);

const props = defineProps<UserIndexProps>();

const currentPage = ref(props.users.current_page);
const currentGlobalFilter = ref(props.filter);
const showDisabledUsers = ref(props.show_disabled);

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
                fetchTableData();
            },
            onError: () => {
                toast.error(props.flash?.error || 'Wystąpił nieoczekiwany błąd podczas wyłączania użytkownika.');
                isAlertDialogOpen.value = false;
                userToDeleteId.value = null;
                userToDeleteName.value = '';
            },
        });
    }
};

watch(showDisabledUsers, (newValue) => {
    router.get(route('users.index', { show_disabled: newValue }), {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['users', 'show_disabled'],
    });
});
const restoreUser = (userId: number) => {
    router.post(route('users.restore', userId), {}, {
        onSuccess: () => {
            toast.success('Użytkownik został pomyślnie przywrócony.');
            fetchTableData();
        },
        onError: () => {
            toast.error('Wystąpił błąd podczas przywracania użytkownika.');
        },
    });
};

const fetchTableData = () => {
    router.get(
        route('users.index'),
        {
            page: currentPage.value,
            filter: currentGlobalFilter.value,
            show_disabled: showDisabledUsers.value,
            sort: props.sort_by,
            direction: props.sort_direction,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['users', 'show_disabled', 'sort_by', 'sort_direction', 'filter'],
        }
    );
};

const handlePageUpdate = (newPage: number) => {
    currentPage.value = newPage;
    fetchTableData();
};

const handleFilterUpdate = (newFilter: string) => {
    currentGlobalFilter.value = newFilter;
    currentPage.value = 1;
    fetchTableData();
};

const handleSortUpdate = (sortData: { column: string, direction: string }) => {
    router.get(
        route('users.index'),
        {
            page: 1,
            filter: currentGlobalFilter.value,
            show_disabled: showDisabledUsers.value,
            sort: sortData.column,
            direction: sortData.direction,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['users', 'show_disabled', 'sort_by', 'sort_direction', 'filter'],
        }
    );
};

const columns: ColumnDef<typeof props.users.data[number]>[] = [
    {
        accessorKey: 'id',
        header: 'ID',
        enableSorting: true,
        enableGlobalFilter: true,
    },
    {
        accessorKey: 'name',
        header: 'Nazwa',
        cell: ({ row }) => h('div', { class: 'capitalize' }, row.getValue('name')),
        enableSorting: true,
        enableGlobalFilter: true,
    },
    {
        accessorKey: 'email',
        header: 'Email',
        cell: ({ row }) => h('div', { class: 'lowercase' }, row.getValue('email')),
        enableSorting: true,
        enableGlobalFilter: true,
    },
    {
        accessorKey: 'role',
        header: 'Rola',
        cell: ({ row }) => {
            const role = row.original.role;
            return h('span', role ? role : 'Brak');
        },
        enableSorting: false, 
        enableGlobalFilter: true,
        filterFn: (row, columnId, filterValue) => {
            const role = row.original.role;
            return role ? role.toLowerCase().includes(filterValue.toLowerCase()) : false;
        }
    },
    {
        accessorKey: 'deleted_at',
        header: 'Status',
        cell: ({ row }) => {
            const status = row.original.deleted_at === null ? 'Aktywny' : 'Wyłączony';
            const colorClass = row.original.deleted_at === null ? 'text-green-600' : 'text-red-600';
            return h('span', { class: colorClass + ' font-medium' }, status);
        },
        enableSorting: false,
        enableGlobalFilter: true,
    },
    {
        id: 'actions',
        header: () => h('div', { class: 'w-full text-right' }, 'Akcje'),
        enableSorting: false,
        enableGlobalFilter: false,
        cell: ({ row }) => {
            const user = row.original;
            return h('div', { class: 'flex justify-end space-x-2 text-right' }, [
                user.deleted_at === null
                    ? h(Link, { href: route('users.edit', user.id) }, () => h(Button, { variant: 'outline', size: 'sm' }, { default: () => 'Edytuj' }))
                    : null,
                user.deleted_at !== null
                    ? h(Button, { variant: 'outline', size: 'sm', onClick: () => restoreUser(user.id) }, { default: () => 'Przywróć' })
                    : null,
                user.deleted_at === null
                    ? h(Button, {
                          variant: 'destructive',
                          size: 'sm',
                          onClick: () => confirmDelete(user.id, user.name),
                          disabled: user.id === (props.auth?.user?.id || undefined) || user.role === 'admin',
                      }, { default: () => 'Wyłącz' })
                    : null,
            ]);
        },
    },
];
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
                <DataTable
                    :columns="columns"
                    :data="props.users.data"
                    :current-page="props.users.current_page"
                    :last-page="props.users.last_page"
                    :total="props.users.total"
                    :per-page="props.users.per_page"
                    @update:page="handlePageUpdate"
                    @update:filter="handleFilterUpdate"
                    @update:sort="handleSortUpdate"
                    :sort-by="props.sort_by" :sort-direction="props.sort_direction"
                />

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
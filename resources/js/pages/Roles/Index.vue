<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type RoleIndexProps } from '@/types';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch, defineAsyncComponent, h } from 'vue';
import { toast } from 'vue-sonner';
import { ColumnDef } from '@tanstack/vue-table';
import { Button } from '@/components/ui/button';
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


const DataTable = defineAsyncComponent(() => import('@/components/DataTable.vue'));

const props = defineProps<RoleIndexProps>();

const currentPage = ref(props.roles.current_page);
const currentGlobalFilter = ref(props.filter);
const showDisabledRoles = ref(props.show_disabled);

const form = useForm({});
const isAlertDialogOpen = ref(false);
const roleToDeleteId = ref<number | null>(null);
const roleToDeleteName = ref<string>('');
const isRoleAssignedToUsers = ref<boolean>(false);

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
                if (props.flash?.error) { toast.error(props.flash.error); }
                else if (props.flash?.success) { toast.success(props.flash.success); }
                isAlertDialogOpen.value = false;
            },
            onError: () => {
                toast.error(props.flash?.error || 'Wystąpił nieoczekiwany błąd.');
            },
        });
    }
};

const restoreRole = (roleId: number) => {
    router.post(route('roles.restore', roleId), {}, {
        onSuccess: () => {
            toast.success('Rola została pomyślnie przywrócona.');
        },
        onError: () => {
            toast.error('Wystąpił błąd podczas przywracania roli.');
        },
    });
};

const fetchTableData = () => {
    router.get(
        route('roles.index'),
        {
            page: currentPage.value,
            filter: currentGlobalFilter.value,
            show_disabled: showDisabledRoles.value,
            sort: props.sort_by,
            direction: props.sort_direction,
        },
        { preserveState: true, preserveScroll: true, only: ['roles', 'show_disabled', 'sort_by', 'sort_direction', 'filter'] }
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
        route('roles.index'),
        {
            page: 1,
            filter: currentGlobalFilter.value,
            show_disabled: showDisabledRoles.value,
            sort: sortData.column,
            direction: sortData.direction,
        },
        { preserveState: true, preserveScroll: true, only: ['roles', 'show_disabled', 'sort_by', 'sort_direction', 'filter'] }
    );
};

watch(showDisabledRoles, (newValue) => {
    router.get(
        route('roles.index'),
        {
            show_disabled: newValue,
            page: 1,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['roles', 'show_disabled'],
        }
    );
});

const columns: ColumnDef<typeof props.roles.data[number]>[] = [
    { accessorKey: 'id', header: 'ID', enableSorting: true },
    { accessorKey: 'name', header: 'Nazwa', enableSorting: true },
    {
        accessorKey: 'deleted_at',
        header: 'Status',
        enableSorting: false,
        cell: ({ row }) => {
            const status = row.original.deleted_at === null ? 'Aktywna' : 'Wyłączona';
            const colorClass = row.original.deleted_at === null ? 'text-green-600' : 'text-red-600';
            return h('span', { class: colorClass + ' font-medium' }, status);
        },
    },
    {
        id: 'actions',
        header: 'Akcje',
        enableSorting: false,
        cell: ({ row }) => {
            const role = row.original;
            return h('div', { class: 'flex justify-end space-x-2' }, [
                role.deleted_at === null
                    ? h(Link, { href: route('roles.edit', role.id) }, () => h(Button, { variant: 'outline', size: 'sm' }, () => 'Edytuj'))
                    : null,
                role.deleted_at !== null
                    ? h(Button, { variant: 'outline', size: 'sm', onClick: () => restoreRole(role.id) }, () => 'Przywróć')
                    : null,
                role.deleted_at === null
                    ? h(Button, {
                        variant: 'destructive',
                        size: 'sm',
                        onClick: () => confirmDelete(role.id, role.name, role.is_assigned_to_users || false),
                        disabled: role.name === 'Kierownik',
                    }, () => 'Wyłącz')
                    : null,
            ]);
        },
    },
];

</script>

<template>

    <Head title="Zarządzanie Rolami" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Lista Ról</h3>
                    <div class="flex items-center space-x-2">
                        <Switch
                            id="show-disabled-roles"
                            :model-value="showDisabledRoles"
                            @update:model-value="showDisabledRoles = $event"
                        />
                        <Label for="show-disabled-roles">Pokaż wyłączone role</Label>
                    </div>
                </div>

                <DataTable :columns="columns" :data="props.roles.data" :current-page="props.roles.current_page"
                    :last-page="props.roles.last_page" :total="props.roles.total" :per-page="props.roles.per_page"
                    @update:page="handlePageUpdate" @update:filter="handleFilterUpdate" @update:sort="handleSortUpdate"
                    :sort-by="props.sort_by ?? null" :sort-direction="props.sort_direction ?? null" />

                <div class="mt-6">
                    <Link :href="route('roles.create')">
                    <Button class="w-full">Dodaj Rolę</Button>
                    </Link>
                </div>
            </div>
        </div>

        <AlertDialog :open="isAlertDialogOpen" @update:open="isAlertDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Czy na pewno chcesz wyłączyć rolę?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Ta akcja spowoduje wyłączenie roli "{{ roleToDeleteName }}".
                        <template v-if="isRoleAssignedToUsers">
                            <br><br>
                            <span class="font-semibold text-red-500">WAŻNE:</span> Rola jest obecnie przypisana do
                            pracowników. Po wyłączeniu, zostanie od nich automatycznie odpięta.
                        </template>
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
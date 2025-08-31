<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type PreferenceIndexProps } from '@/types';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch, h, defineAsyncComponent } from 'vue';
import { toast } from 'vue-sonner';
import { ColumnDef } from '@tanstack/vue-table';
import { Button } from '@/components/ui/button';
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

const DataTable = defineAsyncComponent(() => import('@/components/DataTable.vue'));
const props = defineProps<PreferenceIndexProps>();

const currentPage = ref(props.preferences.current_page);
const currentGlobalFilter = ref(props.filter);
const showInactiveOrDeleted = ref(props.show_inactive_or_deleted);

watch(showInactiveOrDeleted, (newValue) => {
    router.get(route('preferences.index', { show_inactive_or_deleted: newValue, page: 1 }), {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['preferences', 'show_inactive_or_deleted'],
    });
});

const form = useForm({});
const isAlertDialogOpen = ref(false);
const preferenceToDeleteId = ref<number | null>(null);

const confirmDelete = (preferenceId: number) => {
    preferenceToDeleteId.value = preferenceId;
    isAlertDialogOpen.value = true;
};

const deletePreferenceConfirmed = () => {
    if (preferenceToDeleteId.value !== null) {
        form.delete(route('preferences.destroy', preferenceToDeleteId.value), {
            onSuccess: () => {
                toast.success('Preferencja została pomyślnie usunięta.');
                isAlertDialogOpen.value = false;
            },
            onError: () => toast.error('Wystąpił błąd podczas usuwania preferencji.'),
        });
    }
};

const restorePreference = (preferenceId: number) => {
    router.post(route('preferences.restore', preferenceId), {}, {
        onSuccess: () => toast.success('Preferencja została pomyślnie przywrócona.'),
        onError: () => toast.error('Wystąpił błąd podczas przywracania preferencji.'),
    });
};

const fetchTableData = () => {
    router.get(
        route('preferences.index'),
        {
            page: currentPage.value,
            filter: currentGlobalFilter.value,
            show_inactive_or_deleted: showInactiveOrDeleted.value,
            sort: props.sort_by,
            direction: props.sort_direction,
        },
        { preserveState: true, preserveScroll: true, only: ['preferences', 'show_inactive_or_deleted', 'sort_by', 'sort_direction', 'filter'] }
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
        route('preferences.index'),
        {
            page: 1,
            filter: currentGlobalFilter.value,
            show_inactive_or_deleted: showInactiveOrDeleted.value,
            sort: sortData.column,
            direction: sortData.direction,
        },
        { preserveState: true, preserveScroll: true, only: ['preferences', 'show_inactive_or_deleted', 'sort_by', 'sort_direction', 'filter'] }
    );
};

const columns: ColumnDef<typeof props.preferences.data[number]>[] = [
    { accessorKey: 'id', header: 'ID', enableSorting: true },
    { accessorKey: 'date_from', header: 'Data od', enableSorting: true },
    { accessorKey: 'date_to', header: 'Data do', enableSorting: true },
    { accessorKey: 'description', header: 'Opis', enableSorting: true },
    {
        accessorKey: 'availability',
        header: 'Dyspozycja',
        enableSorting: false,
        cell: ({ row }) => {
            const isAvailable = row.original.availability;
            const text = isAvailable ? 'Chcę pracować' : 'Nie mogę pracować';
            const colorClass = isAvailable ? 'text-green-600' : 'text-red-600';
            return h('span', { class: colorClass + ' font-semibold' }, text);
        },
    },
    {
        id: 'status',
        header: 'Status',
        enableSorting: false,
        cell: ({ row }) => {
            const pref = row.original;
            if (pref.deleted_at) return h('span', { class: 'text-red-500 font-semibold' }, 'Usunięta');
            if (pref.is_active) return h('span', { class: 'text-green-500 font-semibold' }, 'Aktywna');
            return h('span', { class: 'text-gray-500 font-semibold' }, 'Nieaktywna');
        },
    },
    {
        id: 'actions',
        header: 'Akcje',
        enableSorting: false,
        cell: ({ row }) => {
            const pref = row.original;
            const actions = [];
            if (pref.is_active && !pref.deleted_at) {
                actions.push(h(Link, { href: route('preferences.edit', pref.id) }, () => h(Button, { variant: 'outline', size: 'sm' }, () => 'Edytuj')));
                actions.push(h(Button, { variant: 'destructive', size: 'sm', onClick: () => confirmDelete(pref.id) }, () => 'Wyłącz'));
            } else if (pref.deleted_at) {
                actions.push(h(Button, { variant: 'outline', size: 'sm', onClick: () => restorePreference(pref.id) }, () => 'Przywróć'));
            }
            return h('div', { class: 'flex justify-end space-x-2' }, actions);
        },
    },
];
</script>

<template>

    <Head title="Moje Preferencje" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Moje Preferencje Grafiku</h3>
                    <div class="flex items-center space-x-2">
                        <Switch id="show-all-preferences" :model-value="showInactiveOrDeleted"
                            @update:model-value="showInactiveOrDeleted = $event" />
                        <Label for="show-all-preferences">Pokaż tylko nieaktualne i wyłączone</Label>
                    </div>
                </div>

                <DataTable :columns="columns" :data="props.preferences.data"
                    :current-page="props.preferences.current_page" :last-page="props.preferences.last_page"
                    :total="props.preferences.total" :per-page="props.preferences.per_page"
                    @update:page="handlePageUpdate" @update:filter="handleFilterUpdate" @update:sort="handleSortUpdate"
                    :sort-by="props.sort_by ?? null" :sort-direction="props.sort_direction ?? null" />

                <div class="mt-6">
                    <Button as-child class="w-full">
                        <Link :href="route('preferences.create')">Dodaj Preferencję</Link>
                    </Button>
                </div>
            </div>
        </div>

        <AlertDialog :open="isAlertDialogOpen" @update:open="isAlertDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Czy na pewno chcesz wyłączyć tę preferencję?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Ta akcja spowoduje wyłączenie preferencji.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="isAlertDialogOpen = false">Anuluj</AlertDialogCancel>
                    <AlertDialogAction @click="deletePreferenceConfirmed"
                        class="bg-red-600 text-white hover:bg-red-700">
                        Wyłącz Preferencję
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
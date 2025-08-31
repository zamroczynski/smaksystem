<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type ShiftTemplateIndexProps } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { ref, watch, h, defineAsyncComponent } from 'vue';
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
const props = defineProps<ShiftTemplateIndexProps>();

const currentPage = ref(props.shiftTemplates.current_page);
const currentGlobalFilter = ref(props.filter);
const showDeleted = ref(props.show_deleted);

watch(showDeleted, (newValue) => {
    router.get(route('shift-templates.index', { show_deleted: newValue, page: 1 }), {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['shiftTemplates', 'show_deleted'],
    });
});

const form = useForm({});
const isAlertDialogOpen = ref(false);
const shiftTemplateToDisableId = ref<number | null>(null);

const confirmDisable = (id: number) => {
    shiftTemplateToDisableId.value = id;
    isAlertDialogOpen.value = true;
};

const disableShiftTemplateConfirmed = () => {
    if (shiftTemplateToDisableId.value !== null) {
        form.delete(route('shift-templates.destroy', shiftTemplateToDisableId.value), {
            onSuccess: () => toast.success('Harmonogram zmian został pomyślnie wyłączony.'),
            onError: () => toast.error('Wystąpił błąd podczas wyłączania harmonogramu.'),
            onFinish: () => isAlertDialogOpen.value = false,
        });
    }
};

const enableShiftTemplate = (id: number) => {
    router.post(route('shift-templates.restore', id), {}, {
        onSuccess: () => toast.success('Harmonogram zmian został pomyślnie włączony.'),
        onError: () => toast.error('Wystąpił błąd podczas włączania harmonogramu.'),
    });
};

const fetchTableData = () => {
    router.get(
        route('shift-templates.index'),
        {
            page: currentPage.value,
            filter: currentGlobalFilter.value,
            show_deleted: showDeleted.value,
            sort: props.sort_by,
            direction: props.sort_direction,
        },
        { preserveState: true, preserveScroll: true, only: ['shiftTemplates', 'show_deleted', 'sort_by', 'sort_direction', 'filter'] }
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
        route('shift-templates.index'),
        {
            page: 1,
            filter: currentGlobalFilter.value,
            show_deleted: showDeleted.value,
            sort: sortData.column,
            direction: sortData.direction,
        },
        { preserveState: true, preserveScroll: true, only: ['shiftTemplates', 'show_deleted', 'sort_by', 'sort_direction', 'filter'] }
    );
};

const columns: ColumnDef<typeof props.shiftTemplates.data[number]>[] = [
    { accessorKey: 'id', header: 'ID', enableSorting: true },
    { accessorKey: 'name', header: 'Nazwa', enableSorting: true },
    { accessorKey: 'time_from', header: 'Od', enableSorting: true },
    { accessorKey: 'time_to', header: 'Do', enableSorting: true },
    { accessorKey: 'required_staff_count', header: 'L. Prac.', enableSorting: true },
    {
        accessorKey: 'deleted_at',
        header: 'Status',
        enableSorting: false,
        cell: ({ row }) => {
            const status = row.original.deleted_at ? 'Wyłączony' : 'Aktywny';
            const colorClass = row.original.deleted_at ? 'text-red-500' : 'text-green-500';
            return h('span', { class: colorClass + ' font-semibold' }, status);
        },
    },
    {
        id: 'actions',
        header: () => h('div', { class: 'w-full text-right' }, 'Akcje'),
        enableSorting: false,
        cell: ({ row }) => {
            const st = row.original;
            return h('div', { class: 'flex justify-end space-x-2' }, [
                st.deleted_at
                    ? h(Button, { variant: 'outline', size: 'sm', onClick: () => enableShiftTemplate(st.id) }, () => 'Przywróć')
                    : [
                        h(Link, { href: route('shift-templates.edit', st.id) }, () => h(Button, { variant: 'outline', size: 'sm' }, () => 'Edytuj')),
                        h(Button, { variant: 'destructive', size: 'sm', onClick: () => confirmDisable(st.id) }, () => 'Wyłącz')
                    ]
            ]);
        },
    },
];
</script>

<template>

    <Head title="Harmonogramy Zmian" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Lista Harmonogramów Zmian</h3>
                    <div class="flex items-center space-x-2">
                        <Switch id="show-deleted" :model-value="showDeleted"
                            @update:model-value="showDeleted = $event" />
                        <Label for="show-inactive-or-deleted">Pokaż tylko wyłączone</Label>
                    </div>
                </div>

                <DataTable :columns="columns" :data="props.shiftTemplates.data"
                    :current-page="props.shiftTemplates.current_page" :last-page="props.shiftTemplates.last_page"
                    :total="props.shiftTemplates.total" :per-page="props.shiftTemplates.per_page"
                    @update:page="handlePageUpdate" @update:filter="handleFilterUpdate" @update:sort="handleSortUpdate"
                    :sort-by="props.sort_by ?? null" :sort-direction="props.sort_direction ?? null" />

                <div class="mt-6">
                    <Button as-child class="w-full">
                        <Link :href="route('shift-templates.create')">Dodaj Harmonogram Zmian</Link>
                    </Button>
                </div>
            </div>
        </div>

        <AlertDialog :open="isAlertDialogOpen" @update:open="isAlertDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Czy na pewno chcesz wyłączyć ten harmonogram zmian?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Ta akcja spowoduje wyłączenie harmonogramu zmian. Będzie on niewidoczny w aktywnych listach,
                        ale będzie można go przywrócić.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="isAlertDialogOpen = false">Anuluj</AlertDialogCancel>
                    <AlertDialogAction @click="disableShiftTemplateConfirmed"
                        class="bg-red-600 text-white hover:bg-red-700">
                        Wyłącz Harmonogram
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
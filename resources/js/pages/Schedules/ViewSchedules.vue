<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type ScheduleViewProps } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, h, defineAsyncComponent } from 'vue';
import { toast } from 'vue-sonner';
import { ColumnDef } from '@tanstack/vue-table';
import { Button } from '@/components/ui/button';

const DataTable = defineAsyncComponent(() => import('@/components/DataTable.vue'));
const props = defineProps<ScheduleViewProps>();

const currentPage = ref(props.schedules.current_page);
const currentGlobalFilter = ref(props.filter);

const fetchTableData = () => {
    router.get(
        route('employee.schedules.index'),
        {
            page: currentPage.value,
            filter: currentGlobalFilter.value,
            sort: props.sort_by,
            direction: props.sort_direction,
        },
        { preserveState: true, preserveScroll: true, only: ['schedules', 'sort_by', 'sort_direction', 'filter'] }
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
        route('employee.schedules.index'),
        {
            page: 1,
            filter: currentGlobalFilter.value,
            sort: sortData.column,
            direction: sortData.direction,
        },
        { preserveState: true, preserveScroll: true, only: ['schedules', 'sort_by', 'sort_direction', 'filter'] }
    );
};

const columns: ColumnDef<typeof props.schedules.data[number]>[] = [
    { accessorKey: 'id', header: 'ID', enableSorting: true },
    { accessorKey: 'name', header: 'Nazwa', enableSorting: true },
    { accessorKey: 'period_start_date', header: 'Miesiąc', enableSorting: true },
    {
        accessorKey: 'status',
        header: 'Status',
        enableSorting: true,
        cell: ({ row }) => {
            const status = row.original.status;
            let text = 'Nieznany';
            let colorClass = 'text-gray-500';
            if (status === 'published') { text = 'Opublikowany'; colorClass = 'text-green-600'; }
            else if (status === 'archived') { text = 'Zarchiwizowany'; colorClass = 'text-red-500'; }
            return h('span', { class: colorClass + ' font-semibold' }, text);
        },
    },
    {
        id: 'actions',
        header: () => h('div', { class: 'w-full text-right' }, 'Akcje'),
        cell: ({ row }) => {
            const schedule = row.original;
            return h('div', { class: 'flex justify-end space-x-2' }, [
                h(Link, { href: route('employee.schedules.show', schedule.id) }, () => h(Button, { variant: 'outline', size: 'sm' }, () => 'Pokaż')),
                h('a', { href: route('employee.schedules.pdf.full', schedule.id), target: '_blank' }, [h(Button, { variant: 'secondary', size: 'sm' }, () => 'Pobierz')])
            ]);
        },
    },
];
</script>

<template>

    <Head title="Grafiki Pracy" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Lista Dostępnych Grafików Pracy
                    </h3>
                </div>

                <DataTable :columns="columns" :data="props.schedules.data" :current-page="props.schedules.current_page"
                    :last-page="props.schedules.last_page" :total="props.schedules.total"
                    :per-page="props.schedules.per_page" @update:page="handlePageUpdate"
                    @update:filter="handleFilterUpdate" @update:sort="handleSortUpdate" :sort-by="props.sort_by ?? null"
                    :sort-direction="props.sort_direction ?? null" />

            </div>
        </div>
    </AppLayout>
</template>
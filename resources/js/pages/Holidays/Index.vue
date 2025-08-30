<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type HolidayIndexProps } from '@/types/holiday';
import { type Holiday } from '@/types/models';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch, h, defineAsyncComponent } from 'vue';
import { ColumnDef } from '@tanstack/vue-table';
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

const DataTable = defineAsyncComponent(() =>
  import('@/components/DataTable.vue')
);

const props = defineProps<HolidayIndexProps>();

const currentPage = ref(props.holidays.current_page);
const currentGlobalFilter = ref(props.filter);
const showArchived = ref(props.show_archived);

const form = useForm({});
const isAlertDialogOpen = ref(false);
const holidayToDelete = ref<Pick<Holiday, 'id' | 'name'> | null>(null);

const confirmDelete = (holiday: Pick<Holiday, 'id' | 'name'>) => {
    holidayToDelete.value = holiday;
    isAlertDialogOpen.value = true;
};

const deleteHolidayConfirmed = () => {
    if (holidayToDelete.value) {
        form.delete(route('holidays.destroy', holidayToDelete.value.id), {
            onSuccess: () => {
                toast.success('Dzień wolny został pomyślnie zarchiwizowany.');
                isAlertDialogOpen.value = false;
                holidayToDelete.value = null;
            },
            onError: (errors) => {
                toast.error(errors.message || 'Wystąpił błąd podczas archiwizacji.');
            },
        });
    }
};

const restoreHoliday = (holidayId: number) => {
    router.post(route('holidays.restore', holidayId), {}, {
        onSuccess: () => toast.success('Dzień wolny został pomyślnie przywrócony.'),
        onError: () => toast.error('Wystąpił błąd podczas przywracania.'),
    });
};

watch(showArchived, (newValue) => {
    router.get(route('holidays.index', { show_archived: newValue }), {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['holidays', 'show_archived'],
    });
});

const fetchTableData = () => {
    router.get(
        route('holidays.index'),
        {
            page: currentPage.value,
            filter: currentGlobalFilter.value,
            show_archived: showArchived.value,
            sort: props.sort_by,
            direction: props.sort_direction,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['holidays', 'filter', 'show_archived', 'sort_by', 'sort_direction'],
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
        route('holidays.index'),
        {
            page: 1,
            filter: currentGlobalFilter.value,
            show_archived: showArchived.value,
            sort: sortData.column,
            direction: sortData.direction,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['holidays', 'filter', 'show_archived', 'sort_by', 'sort_direction'],
        }
    );
};

const columns: ColumnDef<Holiday>[] = [
    {
        accessorKey: 'name',
        header: 'Nazwa',
        enableSorting: true,
    },
    {
        accessorKey: 'date',
        header: 'Data / Reguła',
        cell: ({ row }) => {
            const holiday = row.original;
            if (holiday.date) return h('span', holiday.date);
            if (holiday.day_month) {
                const [month, day] = holiday.day_month.split('-');
                return h('span', `${day}.${month} (Stała, coroczna)`);
            }
            if (holiday.calculation_rule) {
                const { offset } = holiday.calculation_rule;
                const sign = offset >= 0 ? '+' : '-';
                return h('span', `Wielkanoc ${sign} ${Math.abs(offset)} dni`);
            }
            return h('span', { class: 'text-gray-400' }, 'Brak');
        },
        enableSorting: false,
    },
    {
        accessorKey: 'deleted_at',
        header: 'Status',
        cell: ({ row }) => {
            const status = row.original.deleted_at === null ? 'Aktywny' : 'Zarchiwizowany';
            const colorClass = row.original.deleted_at === null ? 'text-green-600' : 'text-red-600';
            return h('span', { class: colorClass + ' font-medium' }, status);
        },
        enableSorting: false,
    },
    {
        id: 'actions',
        header: 'Akcje',
        cell: ({ row }) => {
            const holiday = row.original;
            return h('div', { class: 'flex justify-end space-x-2 text-right' }, [
                holiday.deleted_at === null
                    ? h(Button, { variant: 'outline', size: 'sm' }, { default: () => 'Edytuj' })
                    : null,
                holiday.deleted_at !== null
                    ? h(Button, { variant: 'outline', size: 'sm', onClick: () => restoreHoliday(holiday.id) }, { default: () => 'Przywróć' })
                    : null,
                holiday.deleted_at === null
                    ? h(Button, { variant: 'destructive', size: 'sm', onClick: () => confirmDelete(holiday) }, { default: () => 'Archiwizuj' })
                    : null,
            ]);
        },
    },
];
</script>

<template>
    <Head title="Zarządzanie Dniami Wolnymi" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Lista Dni Wolnych</h3>
                    <div class="flex items-center space-x-2">
                        <Switch
                            id="show-archived-switch"
                            :model-value="showArchived"
                            @update:model-value="showArchived = $event"
                        />
                        <Label for="show-archived-switch">Pokaż zarchiwizowane</Label>
                    </div>
                </div>
                <DataTable
                    :columns="columns"
                    :data="props.holidays.data"
                    :current-page="props.holidays.current_page"
                    :last-page="props.holidays.last_page"
                    :total="props.holidays.total"
                    :per-page="props.holidays.per_page"
                    @update:page="handlePageUpdate"
                    @update:filter="handleFilterUpdate"
                    @update:sort="handleSortUpdate"
                    :sort-by="props.sort_by"
                    :sort-direction="props.sort_direction"
                />

                <div class="mt-6">
                    <Link :href="route('holidays.create')">
                        <Button class="w-full">Dodaj Dzień Wolny</Button>
                    </Link>
                </div>
            </div>
        </div>
        <AlertDialog :open="isAlertDialogOpen" @update:open="isAlertDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Czy na pewno chcesz zarchiwizować ten wpis?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Ta akcja spowoduje ukrycie dnia wolnego "{{ holidayToDelete?.name }}". Będzie go można przywrócić w przyszłości.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="isAlertDialogOpen = false">Anuluj</AlertDialogCancel>
                    <AlertDialogAction @click="deleteHolidayConfirmed" class="bg-red-600 text-white hover:bg-red-700">
                        Archiwizuj
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
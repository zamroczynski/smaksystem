<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type ScheduleIndexProps } from '@/types/schedule';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch, h, defineAsyncComponent } from 'vue';
import { toast } from 'vue-sonner';
import { ColumnDef } from '@tanstack/vue-table';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Button } from '@/components/ui/button';
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
const props = defineProps<ScheduleIndexProps>();

const currentPage = ref(props.schedules.current_page);
const currentGlobalFilter = ref(props.filter);
const showArchived = ref(props.show_archived);

watch(showArchived, (newVal) => {
    router.get(route('schedules.index', { show_archived: newVal, page: 1 }), {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['schedules', 'show_archived'],
    });
});
const form = useForm({});
const isArchiveAlertDialogOpen = ref(false);
const scheduleToArchiveId = ref<number | null>(null);
const isEditAlertDialogOpen = ref(false);
const scheduleToEditId = ref<number | null>(null);

const confirmArchive = (id: number) => {
    scheduleToArchiveId.value = id;
    isArchiveAlertDialogOpen.value = true;
};

const archiveScheduleConfirmed = () => {
    if (scheduleToArchiveId.value !== null) {
        form.delete(route('schedules.destroy', scheduleToArchiveId.value), {
            onSuccess: () => toast.success('Grafik pomyślnie zarchiwizowany.'),
            onError: () => toast.error('Wystąpił błąd podczas archiwizacji.'),
            onFinish: () => isArchiveAlertDialogOpen.value = false,
        });
    }
};

const confirmEdit = (id: number) => {
    scheduleToEditId.value = id;
    isEditAlertDialogOpen.value = true;
};

const continueEdit = () => {
    if (scheduleToEditId.value !== null) {
        router.visit(route('schedules.edit', scheduleToEditId.value));
    }
};

const restoreSchedule = (id: number) => {
    router.post(route('schedules.restore', id), {}, {
        onSuccess: () => toast.success('Grafik pomyślnie przywrócony.'),
        onError: () => toast.error('Wystąpił błąd podczas przywracania.'),
    });
};

const publishSchedule = (id: number) => {
    router.post(route('schedules.publish', id), {}, {
        onSuccess: () => toast.success('Grafik pomyślnie opublikowany.'),
        onError: () => toast.error('Wystąpił błąd podczas publikacji.'),
    });
};

const unpublishSchedule = (id: number) => {
    router.post(route('schedules.unpublish', id), {}, {
        onSuccess: () => toast.success('Status zmieniono na roboczy.'),
        onError: () => toast.error('Wystąpił błąd podczas zmiany statusu.'),
    });
};

const fetchTableData = () => {
    router.get(
        route('schedules.index'),
        {
            page: currentPage.value,
            filter: currentGlobalFilter.value,
            show_archived: showArchived.value,
            sort: props.sort_by,
            direction: props.sort_direction,
        },
        { preserveState: true, preserveScroll: true, only: ['schedules', 'show_archived', 'sort_by', 'sort_direction', 'filter'] }
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
        route('schedules.index'),
        {
            page: 1,
            filter: currentGlobalFilter.value,
            show_archived: showArchived.value,
            sort: sortData.column,
            direction: sortData.direction,
        },
        { preserveState: true, preserveScroll: true, only: ['schedules', 'show_archived', 'sort_by', 'sort_direction', 'filter'] }
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
            else if (status === 'draft') { text = 'Roboczy'; colorClass = 'text-blue-500'; }
            else if (status === 'archived') { text = 'Zarchiwizowany'; colorClass = 'text-red-500'; }
            return h('span', { class: colorClass + ' font-semibold' }, text);
        },
    },
    { accessorKey: 'created_at', header: 'Utworzono', enableSorting: true },
    {
        id: 'actions',
        header: () => h('div', { class: 'w-full text-right' }, 'Akcje'),
        cell: ({ row }) => {
            const schedule = row.original;
            const actions = [];
            if (schedule.status === 'archived') {
                actions.push(h(Button, { variant: 'outline', size: 'sm', onClick: () => restoreSchedule(schedule.id) }, () => 'Przywróć'));
            } else if (schedule.status === 'draft') {
                actions.push(h(Button, { variant: 'outline', size: 'sm', onClick: () => confirmEdit(schedule.id) }, () => 'Edytuj'));
                actions.push(h(Button, { variant: 'outline', size: 'sm', onClick: () => publishSchedule(schedule.id) }, () => 'Opublikuj'));
                actions.push(h(Button, { variant: 'destructive', size: 'sm', onClick: () => confirmArchive(schedule.id) }, () => 'Archiwizuj'));
            } else if (schedule.status === 'published') {
                actions.push(h(Button, { variant: 'outline', size: 'sm', onClick: () => unpublishSchedule(schedule.id) }, () => 'Cofnij publikację'));
                actions.push(h(Button, { variant: 'destructive', size: 'sm', onClick: () => confirmArchive(schedule.id) }, () => 'Archiwizuj'));
            }
            return h('div', { class: 'flex justify-end space-x-2' }, actions);
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
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Lista Grafików Pracy</h3>
                    <div class="flex items-center space-x-2">
                        <Switch id="show-archived" :model-value="showArchived"
                            @update:model-value="showArchived = $event" />
                        <Label for="show-archived">Pokaż tylko zarchiwizowane</Label>
                    </div>
                </div>

                <DataTable :columns="columns" :data="props.schedules.data" :current-page="props.schedules.current_page"
                    :last-page="props.schedules.last_page" :total="props.schedules.total"
                    :per-page="props.schedules.per_page" @update:page="handlePageUpdate"
                    @update:filter="handleFilterUpdate" @update:sort="handleSortUpdate" :sort-by="props.sort_by ?? null"
                    :sort-direction="props.sort_direction ?? null" />

                <div class="mt-6">
                    <Button as-child class="w-full">
                        <Link :href="route('schedules.create')">Dodaj Grafik Pracy</Link>
                    </Button>
                </div>
            </div>
        </div>

        <AlertDialog :open="isArchiveAlertDialogOpen" @update:open="isArchiveAlertDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Czy na pewno chcesz zarchiwizować ten grafik pracy?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Ta akcja spowoduje zarchiwizowanie grafiku pracy. Będzie on niewidoczny w aktywnych listach,
                        ale będzie można go przywrócić. Pamiętaj, że zarchiwizowany grafik nie może być edytowany ani
                        przypisywany.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="isArchiveAlertDialogOpen = false">Anuluj</AlertDialogCancel>
                    <AlertDialogAction @click="archiveScheduleConfirmed" class="bg-red-600 text-white hover:bg-red-700">
                        Archiwizuj Grafik
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <AlertDialog :open="isEditAlertDialogOpen" @update:open="isEditAlertDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Uruchomienie narzędzia do edycji grafików</AlertDialogTitle>
                    <AlertDialogDescription>
                        Narzędzie do edycji grafików pracy jest bardzo rozbudowane i jego załadowanie może zająć dłuższą
                        chwilę.
                        Prosimy o cierpliwość.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="isEditAlertDialogOpen = false">Anuluj</AlertDialogCancel>
                    <AlertDialogAction @click="continueEdit">
                        Kontynuuj
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
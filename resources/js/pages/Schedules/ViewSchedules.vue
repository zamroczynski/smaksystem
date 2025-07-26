<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { toast } from 'vue-sonner';
import Pagination from '@/components/Pagination.vue';
import { computed } from 'vue';

interface Schedule {
    id: number;
    name: string;
    period_start_date: string;
    status: 'published' | 'archived';
}

interface SchedulesPaginated {
    data: Schedule[];
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
}

const props = defineProps<{
    schedules: SchedulesPaginated;
    flash?: {
        success?: string;
        error?: string;
    };
}>();

import { watch } from 'vue';
watch(
    () => props.flash,
    (newVal) => {
        if (newVal?.success) {
            toast.success(newVal.success);
        } else if (newVal?.error) {
            toast.error(newVal.error);
        }
    },
    { deep: true },
);


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Panel nawigacyjny',
        href: '/dashboard',
    },
    {
        title: 'Grafiki Pracy',
        href: route('employee.schedules.index'),
    },
];

const getShowScheduleLink = (scheduleId: number) => {
    return route('employee.schedules.show', scheduleId);
};

const getDownloadScheduleLink = (scheduleId: number) => {
    return route('employee.schedules.pdf.full', scheduleId);
};
</script>

<template>
    <Head title="Grafiki Pracy" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Lista Dostępnych Grafików Pracy</h3>
                </div>

                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[100px]">ID</TableHead>
                                <TableHead>Nazwa</TableHead>
                                <TableHead>Miesiąc</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Akcje</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="props.schedules.data.length === 0">
                                <TableCell colspan="5" class="text-center py-4 text-gray-500">
                                    Brak grafików pracy do wyświetlenia.
                                </TableCell>
                            </TableRow>
                            <TableRow v-for="schedule in props.schedules.data" :key="schedule.id">
                                <TableCell class="font-medium">{{ schedule.id }}</TableCell>
                                <TableCell>{{ schedule.name }}</TableCell>
                                <TableCell>{{ schedule.period_start_date }}</TableCell>
                                <TableCell>
                                    <span v-if="schedule.status === 'published'" class="text-green-600 font-semibold">Opublikowany</span>
                                    <span v-else-if="schedule.status === 'archived'" class="text-red-500 font-semibold">Zarchiwizowany</span>
                                    <span v-else class="text-gray-500">Nieznany</span>
                                </TableCell>
                                <TableCell class="text-right flex justify-end space-x-2">
                                    <Button as-child variant="outline" size="sm">
                                        <Link :href="getShowScheduleLink(schedule.id)">Pokaż</Link>
                                    </Button>

                                    <Button as-child variant="secondary" size="sm">
                                        <a :href="getDownloadScheduleLink(schedule.id)" target="_blank">Pobierz</a>
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <Pagination :links="props.schedules.links" class="mt-6" />
            </div>
        </div>
    </AppLayout>
</template>
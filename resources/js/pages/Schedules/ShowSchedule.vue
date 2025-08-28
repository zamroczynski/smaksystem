<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { type ScheduleShowProps } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { computed } from 'vue';

const props = defineProps<ScheduleShowProps>();

const authUser = computed(() => usePage().props.auth.user);

const monthYear = computed(() => {
    const date = new Date(props.scheduleData.schedule.period_start_date);
    return new Intl.DateTimeFormat('pl-PL', { year: 'numeric', month: 'long' }).format(date);
});

const chunkArray = <T>(array: T[], chunkSize: number): T[][] => {
    const chunks: T[][] = [];
    for (let i = 0; i < array.length; i += chunkSize) {
        chunks.push(array.slice(i, i + chunkSize));
    }
    return chunks;
};

const monthDayChunks = computed(() => {
    return chunkArray(props.scheduleData.monthDays, 7);
});

const getAssignmentsForCell = (shiftTemplateId: number, date: string, position: number) => {
    const key = `${shiftTemplateId}_${date}_${position}`;
    return props.scheduleData.assignments[key] || [];
};
</script>

<template>

    <Head :title="`Grafik: ${props.scheduleData.schedule.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <Card class="w-full">
                <CardHeader>
                    <CardTitle>
                        Grafik Pracy: {{ props.scheduleData.schedule.name }}
                    </CardTitle>
                    <CardDescription>
                        Miesiąc: {{ monthYear }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <template v-for="(dayChunk, chunkIndex) in monthDayChunks" :key="chunkIndex">
                            <Table class="min-w-full mb-4">
                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="w-[150px] sticky left-0 bg-white dark:bg-gray-800 z-10">Tydzień {{ chunkIndex + 1 }}</TableHead>
                                        <template v-for="day in dayChunk" :key="day.date">
                                            <TableHead :class="{
                                                'bg-red-100 dark:bg-red-900': day.is_sunday,
                                                'bg-yellow-100 dark:bg-yellow-900': day.is_saturday,
                                            }">
                                                {{ day.day_number }}
                                            </TableHead>
                                        </template>
                                    </TableRow>
                                    <TableRow>
                                        <TableHead class="w-[150px] sticky left-0 bg-white dark:bg-gray-800 z-10 text-xs text-gray-500">Dzień Tygodnia</TableHead>
                                        <template v-for="day in dayChunk" :key="day.date + '-name'">
                                            <TableHead class="text-xs font-normal"
                                                :class="{
                                                    'bg-red-100 dark:bg-red-900': day.is_sunday,
                                                    'bg-yellow-100 dark:bg-yellow-900': day.is_saturday,
                                                }">
                                                {{ day.day_name_short }}
                                            </TableHead>
                                        </template>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <template v-for="shiftTemplate in props.scheduleData.shiftTemplates" :key="shiftTemplate.id">
                                        <TableRow>
                                            <TableCell class="font-medium sticky left-0 bg-white dark:bg-gray-800 z-10">
                                                {{ shiftTemplate.name }} ({{ shiftTemplate.time_from.substring(0, 5) }} - {{ shiftTemplate.time_to.substring(0, 5) }})
                                            </TableCell>
                                            <template v-for="day in dayChunk" :key="day.date">
                                                <TableCell :class="{
                                                    'bg-red-50 dark:bg-red-950': day.is_sunday,
                                                    'bg-yellow-50 dark:bg-yellow-950': day.is_saturday,
                                                }">
                                                    <div v-for="pos in shiftTemplate.required_staff_count" :key="pos" class="mb-1 last:mb-0">
                                                        <template v-if="getAssignmentsForCell(shiftTemplate.id, day.date, pos).length > 0">
                                                            <div v-for="assignment in getAssignmentsForCell(shiftTemplate.id, day.date, pos)" :key="assignment.user_id"
                                                                :class="{ 'font-semibold text-blue-600': authUser && assignment.user_id === authUser.id }"
                                                                class="text-xs">
                                                                {{ assignment.user_name }}
                                                            </div>
                                                        </template>
                                                        <template v-else>
                                                            <div class="text-xs text-gray-400">-</div>
                                                        </template>
                                                    </div>
                                                </TableCell>
                                            </template>
                                        </TableRow>
                                    </template>
                                </TableBody>
                            </Table>
                        </template>
                    </div>
                </CardContent>
            </Card>

            <div class="mt-4 flex justify-end">
                <Button as-child variant="outline">
                    <Link :href="route('employee.schedules.index')">Wróć do listy grafików</Link>
                </Button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { toast } from 'vue-sonner';
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
import { ref, watch } from 'vue';
import { type ScheduleIndexProps } from '@/types/schedule';

const props = defineProps<ScheduleIndexProps>();

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

const showArchived = ref(props.show_archived);

watch(showArchived, (newVal) => {
    router.get(route('schedules.index', { show_archived: newVal }), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
});

const isArchiveAlertDialogOpen = ref(false);
const scheduleToArchiveId = ref<number | null>(null);

const confirmArchive = (id: number) => {
    scheduleToArchiveId.value = id;
    isArchiveAlertDialogOpen.value = true;
};

const archiveScheduleConfirmed = () => {
    if (scheduleToArchiveId.value !== null) {
        useForm({}).delete(route('schedules.destroy', scheduleToArchiveId.value), {
            onSuccess: () => {
                toast.success('Grafik pracy został pomyślnie zarchiwizowany.');
                isArchiveAlertDialogOpen.value = false;
                scheduleToArchiveId.value = null;
                router.get(route('schedules.index', { show_archived: showArchived.value }), {}, {
                    preserveState: true,
                    preserveScroll: true,
                    only: ['schedules', 'show_archived'],
                });
            },
            onError: () => {
                toast.error('Wystąpił błąd podczas archiwizacji grafiku pracy.');
                isArchiveAlertDialogOpen.value = false;
                scheduleToArchiveId.value = null;
            },
        });
    }
};

const isEditAlertDialogOpen = ref(false);
const scheduleToEditId = ref<number | null>(null);

const confirmEdit = (id: number) => {
    scheduleToEditId.value = id;
    isEditAlertDialogOpen.value = true;
};

const continueEdit = () => {
    if (scheduleToEditId.value !== null) {
        router.visit(route('schedules.edit', scheduleToEditId.value));
        isEditAlertDialogOpen.value = false;
        scheduleToEditId.value = null;
    }
};

const restoreSchedule = (id: number) => {
    router.post(route('schedules.restore', id), {}, {
        onSuccess: () => {
            toast.success('Grafik pracy został pomyślnie przywrócony.');
            router.get(route('schedules.index', { show_archived: showArchived.value }), {}, {
                preserveState: true,
                preserveScroll: true,
                only: ['schedules', 'show_archived'],
            });
        },
        onError: () => {
            toast.error('Wystąpił błąd podczas przywracania grafiku pracy.');
        },
    });
};

const publishSchedule = (id: number) => {
    router.post(route('schedules.publish', id), {}, {
        onSuccess: () => {
            toast.success('Grafik pracy został pomyślnie opublikowany.');
            router.get(route('schedules.index', { show_archived: showArchived.value }), {}, {
                preserveState: true,
                preserveScroll: true,
                only: ['schedules', 'show_archived'],
            });
        },
        onError: () => {
            toast.error('Wystąpił błąd podczas publikacji grafiku pracy.');
        },
    });
};

const unpublishSchedule = (id: number) => {
    router.post(route('schedules.unpublish', id), {}, {
        onSuccess: () => {
            toast.success('Grafik pracy został przestawiony na status roboczy.');
            router.get(route('schedules.index', { show_archived: showArchived.value }), {}, {
                preserveState: true,
                preserveScroll: true,
                only: ['schedules', 'show_archived'],
            });
        },
        onError: () => {
            toast.error('Wystąpił błąd podczas przestawiania statusu grafiku.');
        },
    });
};
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

                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[100px]">ID</TableHead>
                                <TableHead>Nazwa</TableHead>
                                <TableHead>Miesiąc</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Utworzono</TableHead>
                                <TableHead>Zaktualizowano</TableHead>
                                <TableHead class="text-right">Akcje</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="props.schedules.data.length === 0">
                                <TableCell colspan="7" class="text-center py-4 text-gray-500">
                                    Brak grafików pracy do wyświetlenia.
                                </TableCell>
                            </TableRow>
                            <TableRow v-for="schedule in props.schedules.data" :key="schedule.id">
                                <TableCell class="font-medium">{{ schedule.id }}</TableCell>
                                <TableCell>{{ schedule.name }}</TableCell>
                                <TableCell>{{ schedule.period_start_date }}</TableCell>
                                <TableCell>
                                    <span v-if="schedule.status === 'published'"
                                        class="text-green-600 font-semibold">Opublikowany</span>
                                    <span v-else-if="schedule.status === 'draft'"
                                        class="text-blue-500 font-semibold">Roboczy</span>
                                    <span v-else-if="schedule.status === 'archived'"
                                        class="text-red-500 font-semibold">Zarchiwizowany</span>
                                    <span v-else class="text-gray-500">Nieznany</span>
                                </TableCell>
                                <TableCell>{{ schedule.created_at }}</TableCell>
                                <TableCell>{{ schedule.updated_at }}</TableCell>
                                <TableCell class="text-right flex justify-end space-x-2">
                                    <template v-if="schedule.status === 'archived'">
                                        <Button variant="outline" size="sm" @click="restoreSchedule(schedule.id)">
                                            Przywróć
                                        </Button>
                                    </template>
                                    <template v-else-if="schedule.status === 'draft'">
                                        <Button variant="outline" size="sm"
                                            @click="confirmEdit(schedule.id)">Edytuj</Button>
                                        <Button variant="outline" size="sm" @click="publishSchedule(schedule.id)">
                                            Opublikuj
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="confirmArchive(schedule.id)">
                                            Archiwizuj
                                        </Button>
                                    </template>
                                    <template v-else-if="schedule.status === 'published'">
                                        <Button variant="outline" size="sm" @click="unpublishSchedule(schedule.id)">
                                            Cofnij publikację
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="confirmArchive(schedule.id)">
                                            Archiwizuj
                                        </Button>
                                    </template>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <Pagination :links="props.schedules.links" class="mt-6" />

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
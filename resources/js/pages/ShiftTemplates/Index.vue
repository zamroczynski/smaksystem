<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type ShiftTemplateIndexProps } from '@/types';
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

const props = defineProps<ShiftTemplateIndexProps>();

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

const showInactiveOrDeleted = ref(props.show_deleted);

watch(showInactiveOrDeleted, (newVal) => {
    router.get(route('shift-templates.index', { show_deleted: newVal }), {}, { 
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
});

const isAlertDialogOpen = ref(false);
const shiftTemplateToDisableId = ref<number | null>(null);

const confirmDisable = (id: number) => {
    shiftTemplateToDisableId.value = id;
    isAlertDialogOpen.value = true;
};

const disableShiftTemplateConfirmed = () => {
    if (shiftTemplateToDisableId.value !== null) {
        useForm({}).delete(route('shift-templates.destroy', shiftTemplateToDisableId.value), {
            onSuccess: () => {
                toast.success('Harmonogram zmian został pomyślnie wyłączony.');
                isAlertDialogOpen.value = false;
                shiftTemplateToDisableId.value = null;
                router.get(route('shift-templates.index', { show_deleted: showInactiveOrDeleted.value }), {}, {
                    preserveState: true,
                    preserveScroll: true,
                    only: ['shiftTemplates', 'show_deleted'], // Dodano 'only' dla optymalizacji
                });
            },
            onError: () => {
                toast.error('Wystąpił błąd podczas wyłączania harmonogramu zmian.');
                isAlertDialogOpen.value = false;
                shiftTemplateToDisableId.value = null;
            },
        });
    }
};

const enableShiftTemplate = (id: number) => {
    router.post(route('shift-templates.restore', id), {}, {
        onSuccess: () => {
            toast.success('Harmonogram zmian został pomyślnie włączony.');
            // Odśwież widok po przywróceniu, z zachowaniem stanu paginacji/filtrów
            router.get(route('shift-templates.index', { show_deleted: showInactiveOrDeleted.value }), {}, {
                preserveState: true,
                preserveScroll: true,
                only: ['shiftTemplates', 'show_deleted'], // Dodano 'only' dla optymalizacji
            });
        },
        onError: () => {
            toast.error('Wystąpił błąd podczas włączania harmonogramu zmian.');
        },
    });
};
</script>

<template>
    <Head title="Harmonogramy Zmian" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Lista Harmonogramów Zmian</h3>
                    <div class="flex items-center space-x-2">
                        <Switch id="show-inactive-or-deleted" :model-value="showInactiveOrDeleted"
                            @update:model-value="showInactiveOrDeleted = $event" />
                        <Label for="show-inactive-or-deleted">Pokaż tylko wyłączone</Label>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[100px]">ID</TableHead>
                                <TableHead>Nazwa</TableHead>
                                <TableHead>Od</TableHead>
                                <TableHead>Do</TableHead>
                                <TableHead>Czas trwania (godziny)</TableHead>
                                <TableHead>Wymagana liczba pracowników</TableHead>
                                <TableHead class="text-center">Status</TableHead>
                                <TableHead class="text-right">Akcje</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="props.shiftTemplates.data.length === 0">
                                <TableCell colspan="8" class="text-center py-4 text-gray-500">
                                    Brak harmonogramów zmian do wyświetlenia.
                                </TableCell>
                            </TableRow>
                            <TableRow v-for="shiftTemplate in props.shiftTemplates.data" :key="shiftTemplate.id">
                                <TableCell class="font-medium">{{ shiftTemplate.id }}</TableCell>
                                <TableCell>{{ shiftTemplate.name }}</TableCell>
                                <TableCell>{{ shiftTemplate.time_from }}</TableCell>
                                <TableCell>{{ shiftTemplate.time_to }}</TableCell>
                                <TableCell>{{ shiftTemplate.duration_hours }}</TableCell>
                                <TableCell>{{ shiftTemplate.required_staff_count }}</TableCell>
                                <TableCell class="text-center">
                                    <span v-if="shiftTemplate.deleted_at" class="text-red-500 font-semibold">Wyłączony</span>
                                    <span v-else class="text-green-500 font-semibold">Aktywny</span>
                                </TableCell>
                                <TableCell class="text-right flex justify-end space-x-2">
                                    <template v-if="shiftTemplate.deleted_at">
                                        <Button variant="outline" size="sm" @click="enableShiftTemplate(shiftTemplate.id)">
                                            Przywróć
                                        </Button>
                                    </template>
                                    <template v-else>
                                        <Button as-child variant="outline" size="sm">
                                            <Link :href="route('shift-templates.edit', shiftTemplate.id)">Edytuj</Link>
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="confirmDisable(shiftTemplate.id)">
                                            Wyłącz
                                        </Button>
                                    </template>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <Pagination :links="props.shiftTemplates.links" class="mt-6" />

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
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
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
import { ref, watch, computed } from 'vue';

// Definicja propsów
const props = defineProps<{
    preferences: {
        data: Array<{
            id: number;
            description: string | null;
            date_from: string;
            date_to: string;
            is_active: boolean;
            deleted_at: string | null;
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        from: number;
        last_page: number;
        per_page: number;
        to: number;
        total: number;
    };
    flash?: {
        success?: string;
        error?: string;
    };
    show_inactive_or_deleted: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Panel nawigacyjny',
        href: '/dashboard',
    },
    {
        title: 'Moje Preferencje',
        href: '/preferences',
    },
];

const showInactiveOrDeleted = ref(props.show_inactive_or_deleted);

watch(showInactiveOrDeleted, (newValue) => {
    router.get(route('preferences.index', { show_inactive_or_deleted: newValue }), {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['preferences', 'show_inactive_or_deleted'],
    });
});

const isAlertDialogOpen = ref(false);
const preferenceToDeleteId = ref<number | null>(null);

const confirmDelete = (preferenceId: number) => {
    preferenceToDeleteId.value = preferenceId;
    isAlertDialogOpen.value = true;
};

const deletePreferenceConfirmed = () => {
    if (preferenceToDeleteId.value !== null) {
        useForm({}).delete(route('preferences.destroy', preferenceToDeleteId.value), {
            onSuccess: () => {
                toast.success('Preferencja została pomyślnie usunięta.');
                isAlertDialogOpen.value = false;
                preferenceToDeleteId.value = null;
                router.get(route('preferences.index', { show_inactive_or_deleted: showInactiveOrDeleted.value }), {}, {
                    preserveState: true,
                    preserveScroll: true,
                    only: ['preferences', 'show_inactive_or_deleted'],
                });
            },
            onError: () => {
                toast.error('Wystąpił błąd podczas usuwania preferencji.');
                isAlertDialogOpen.value = false;
                preferenceToDeleteId.value = null;
            },
        });
    }
};

const restorePreference = (preferenceId: number) => {
    router.post(route('preferences.restore', preferenceId), {}, {
        onSuccess: () => {
            toast.success('Preferencja została pomyślnie przywrócona.');
            router.get(route('preferences.index', { show_inactive_or_deleted: showInactiveOrDeleted.value }), {}, {
                preserveState: true,
                preserveScroll: true,
                only: ['preferences', 'show_inactive_or_deleted'],
            });
        },
        onError: () => {
            toast.error('Wystąpił błąd podczas przywracania preferencji.');
        },
    });
};
</script>

<template>
    <Head title="Moje Preferencje" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Moje Preferencje Grafiku</h3>
                    <div class="flex items-center space-x-2">
                        <Switch id="show-all-preferences" :model-value="showInactiveOrDeleted" @update:model-value="showInactiveOrDeleted = $event" />
                        <Label for="show-all-preferences">Pokaż tylko nieaktualne i wyłączone</Label>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[100px]">ID</TableHead>
                                <TableHead>Opis</TableHead>
                                <TableHead>Data od</TableHead>
                                <TableHead>Data do</TableHead>
                                <TableHead class="text-right">Akcje</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="preference in props.preferences.data" :key="preference.id">
                                <TableCell class="font-medium">{{ preference.id }}</TableCell>
                                <TableCell>{{ preference.description || 'Brak opisu' }}</TableCell>
                                <TableCell>{{ preference.date_from }}</TableCell>
                                <TableCell>{{ preference.date_to }}</TableCell>
                                <TableCell class="text-right flex justify-end space-x-2">
                                    <template v-if="preference.is_active">
                                        <Button as-child variant="outline" size="sm">
                                            <Link :href="route('preferences.edit', preference.id)">Edytuj</Link>
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="confirmDelete(preference.id)">
                                            Wyłącz
                                        </Button>
                                    </template>
                                    <template v-else-if="preference.deleted_at !== null && showInactiveOrDeleted">
                                        <Button variant="outline" size="sm" @click="restorePreference(preference.id)">
                                            Przywróć
                                        </Button>
                                    </template>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="props.preferences.data.length === 0">
                                <TableCell colspan="5" class="text-center text-gray-500">Brak preferencji do wyświetlenia.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <Pagination :links="props.preferences.links" class="mt-6" />

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
                    <AlertDialogAction @click="deletePreferenceConfirmed" class="bg-red-600 text-white hover:bg-red-700">
                        Wyłącz Preferencję
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
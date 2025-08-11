<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { toast } from 'vue-sonner';
import { ref, watch, computed } from 'vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui/table';
import { cn } from '@/utils';

interface UserPreferences {
    [date: string]: boolean;
}

interface EditProps {
    schedule: {
        id: number;
        name: string;
        period_start_date: string;
        status: string;
    };
    assignedShiftTemplates: {
        id: number;
        name: string;
        time_from: string;
        time_to: string;
        duration_hours: number;
        required_staff_count: number;
    }[];
    users: {
        id: number;
        name: string;
    }[];
    initialAssignments: Record<string, number | null>;
    monthDays: {
        date: string;
        day_number: number;
        is_sunday: boolean;
        is_saturday: boolean;
        is_holiday: boolean;
    }[];
    preferences: Record<string, UserPreferences>;
    breadcrumbs: BreadcrumbItem[];
    flash?: {
        success?: string;
        error?: string;
    };
}

const props = defineProps<EditProps>();

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

const form = useForm({
    name: props.schedule.name,
    period_start_date: props.schedule.period_start_date,
    status: props.schedule.status,
    assignments: Object.fromEntries(
        Object.entries(props.initialAssignments).map(([key, value]) => [key, value !== null ? String(value) : null])
    ) as Record<string, string | null>,
});

const localValidationErrors = ref<Record<string, string | null>>({});

const editingCell = ref<{ shiftTemplateId: number; date: string; position: number } | null>(null);

const activateEdit = (shiftTemplateId: number, date: string, position: number) => {
    editingCell.value = { shiftTemplateId, date, position };
};

const isEditing = (shiftTemplateId: number, date: string, position: number): boolean => {
    return (
        editingCell.value?.shiftTemplateId === shiftTemplateId &&
        editingCell.value?.date === date &&
        editingCell.value?.position === position
    );
};

const deactivateEdit = () => {
    editingCell.value = null;
};

// Funkcja do dzielenia tablicy na kawałki (chunks)
const chunkArray = <T>(array: T[], chunkSize: number): T[][] => {
    const chunks: T[][] = [];
    for (let i = 0; i < array.length; i += chunkSize) {
        chunks.push(array.slice(i, i + chunkSize));
    }
    return chunks;
};

// Dzielenie dni miesiąca na 7-dniowe bloki
const monthDayChunks = computed(() => {
    return chunkArray(props.monthDays, 7);
});

const getAssignedUser = (shiftTemplateId: number, date: string, position: number): string | null => {
    const key = `${shiftTemplateId}_${date}_${position}`;
    const assignedId = form.assignments[key];
    return assignedId !== undefined && assignedId !== null ? String(assignedId) : "-1";
};

const setAssignment = (shiftTemplateId: number, date: string, position: number, userId: string | null) => {
    const key = `${shiftTemplateId}_${date}_${position}`;
    if (userId === "-1" || userId === null || userId === '') {
        const newAssignments = { ...form.assignments };
        delete newAssignments[key];
        form.assignments = newAssignments;
    } else {
        form.assignments = {
            ...form.assignments,
            [key]: userId,
        };
    }
    localValidationErrors.value[key] = null;
    deactivateEdit();
};

const getAssignmentError = (shiftTemplateId: number, date: string, position: number): string | null => {
    const compositeKey = `${shiftTemplateId}_${date}_${position}`;
    if (localValidationErrors.value[compositeKey]) {
        return localValidationErrors.value[compositeKey];
    }

    for (const errorKey in form.errors) {
        if (errorKey.startsWith('assignments.') && errorKey.endsWith('.user_id')) {
            const assignmentsAsArray = Object.entries(form.assignments)
                .filter(([key, value]) => value !== null && value !== "-1" && key===key)
                .map(([key, value]) => {
                    const parts = key.split('_');
                    return {
                        shift_template_id: parseInt(parts[0]),
                        assignment_date: parts[1],
                        position: parseInt(parts[2]),
                        user_id: parseInt(value as string),
                        compositeKey: key
                    };
                });

            const errorIndex = parseInt(errorKey.split('.')[1]);

            if (!isNaN(errorIndex) && assignmentsAsArray[errorIndex] && assignmentsAsArray[errorIndex].compositeKey === compositeKey) {
                 return form.errors[errorKey as keyof typeof form.errors];
            }
        }
    }
    return null;
};

const getUserAvailability = (userId: number, date: string): boolean | null => {
    if (props.preferences[userId]) {
        const availability = props.preferences[userId][date];
        if (typeof availability === 'boolean') {
            return availability;
        }
    }
    return null;
};

const getAvailabilityClass = (userId: number, date: string) => {
    const availability = getUserAvailability(userId, date);
    if (availability === true) {
        return 'text-green-600 font-semibold';
    } else if (availability === false) {
        return 'text-red-600 font-semibold';
    }
    return '';
};

const submit = () => {
    localValidationErrors.value = {};

    form.transform((data) => {
        const transformedAssignments: {
            shift_template_id: number;
            assignment_date: string;
            position: number;
            user_id: number | null;
        }[] = [];

        for (const compositeKey in data.assignments) {
            const userIdAsString = data.assignments[compositeKey];
            const parts = compositeKey.split('_');

            if (parts.length === 3) {
                const shiftTemplateId = parseInt(parts[0]);
                const assignmentDate = parts[1];
                const position = parseInt(parts[2]);

                if (userIdAsString !== null && userIdAsString !== "-1") {
                    transformedAssignments.push({
                        shift_template_id: shiftTemplateId,
                        assignment_date: assignmentDate,
                        position: position,
                        user_id: parseInt(userIdAsString),
                    });
                }
            }
        }

        return {
            name: data.name,
            period_start_date: data.period_start_date,
            status: data.status,
            assignments: transformedAssignments,
        };
    }).put(route('schedules.update', props.schedule.id), {
        onSuccess: () => {
            toast.success('Grafik pracy został pomyślnie zaktualizowany.');
            localValidationErrors.value = {};
        },
        onError: (errors) => {
            console.error('Błędy walidacji:', errors);
            for (const key in errors) {
                if (key.startsWith('assignments.')) {
                    toast.error('Wystąpiły błędy walidacji w przypisaniach. Sprawdź wszystkie pola.');
                } else {
                    toast.error((errors as Record<string, string>)[key]);
                }
            }
        },
    });
};
</script>

<template>
    <Head :title="`Edycja grafiku: ${props.schedule.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <Card class="w-full">
                <CardHeader>
                    <CardTitle>
                        Edycja Grafiku Pracy: {{ props.schedule.name }}
                    </CardTitle>
                    <CardDescription>
                        Miesiąc: {{ new Date(props.schedule.period_start_date).toLocaleString('pl-PL', { month: 'long', year: 'numeric' }) }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit">
                        <div class="overflow-x-auto pb-4">
                            <div v-for="(dayChunk, chunkIndex) in monthDayChunks" :key="chunkIndex" class="mb-8">
                                <h4 class="text-md font-semibold mb-2">Tydzień {{ chunkIndex + 1 }}</h4>
                                <Table class="min-w-full">
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead class="w-[150px] sticky left-0 bg-white dark:bg-gray-800 z-10">Zmiana</TableHead>
                                            <template v-for="day in dayChunk" :key="day.date">
                                                <TableHead :class="{
                                                    'bg-red-100 dark:bg-red-900': day.is_sunday || day.is_holiday,
                                                    'bg-yellow-100 dark:bg-yellow-900': day.is_saturday,
                                                }">
                                                    {{ day.day_number }}
                                                </TableHead>
                                            </template>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <template v-for="shiftTemplate in props.assignedShiftTemplates" :key="shiftTemplate.id">
                                            <TableRow>
                                                <TableCell class="font-medium sticky left-0 bg-white dark:bg-gray-800 z-10">
                                                    {{ shiftTemplate.name }} ({{ shiftTemplate.time_from.substring(0, 5) }} - {{ shiftTemplate.time_to.substring(0, 5) }})
                                                </TableCell>
                                                <TableCell
                                                    v-for="day in dayChunk"
                                                    :key="day.date"
                                                    :class="{
                                                        'bg-red-50 dark:bg-red-950': day.is_sunday || day.is_holiday,
                                                        'bg-yellow-50 dark:bg-yellow-950': day.is_saturday,
                                                    }"
                                                >
                                                    <div v-for="pos in shiftTemplate.required_staff_count" :key="pos" class="mb-2 last:mb-0">
                                                        <div
                                                            v-if="!isEditing(shiftTemplate.id, day.date, pos)"
                                                            @click="activateEdit(shiftTemplate.id, day.date, pos)"
                                                            class="p-2 border rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                                                            :class="cn(
                                                                getAssignmentError(shiftTemplate.id, day.date, pos) ? 'border-red-500' : 'border-gray-200 dark:border-gray-700',
                                                                getAvailabilityClass(parseInt(getAssignedUser(shiftTemplate.id, day.date, pos) || '-1'), day.date)
                                                            )"
                                                        >
                                                            {{ users.find(u => String(u.id) === getAssignedUser(shiftTemplate.id, day.date, pos))?.name || 'Brak' }}
                                                        </div>

                                                        <Select
                                                            v-else
                                                            :model-value="getAssignedUser(shiftTemplate.id, day.date, pos)"
                                                            @update:model-value="val => setAssignment(shiftTemplate.id, day.date, pos, val)"
                                                            @update:open="open => !open && deactivateEdit()"
                                                            :default-open="true"
                                                        >
                                                            <SelectTrigger :class="cn(
                                                                'w-full',
                                                                getAssignmentError(shiftTemplate.id, day.date, pos) ? 'border-red-500' : ''
                                                            )">
                                                                <SelectValue :placeholder="`Wybierz (${pos})`">
                                                                    {{ users.find(u => String(u.id) === getAssignedUser(shiftTemplate.id, day.date, pos))?.name || `Wybierz (${pos})` }}
                                                                </SelectValue>
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectItem value="-1">
                                                                    Brak
                                                                </SelectItem>
                                                                <SelectItem
                                                                    v-for="user in props.users"
                                                                    :key="user.id"
                                                                    :value="String(user.id)"
                                                                    :class="getAvailabilityClass(user.id, day.date)"
                                                                >
                                                                    {{ user.name }}
                                                                </SelectItem>
                                                            </SelectContent>
                                                        </Select>
                                                        <div v-if="getAssignmentError(shiftTemplate.id, day.date, pos)"
                                                            class="text-red-500 text-xs mt-1">
                                                            {{ getAssignmentError(shiftTemplate.id, day.date, pos) }}
                                                        </div>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        </template>
                                    </TableBody>
                                </Table>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2">
                            <Button variant="outline" type="button" as-child>
                                <Link :href="route('schedules.index')">Anuluj</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing || Object.values(localValidationErrors).some(error => error !== null)">
                                {{ form.processing ? 'Zapisywanie...' : 'Zapisz Zmiany' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
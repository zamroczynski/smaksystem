<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
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


interface ScheduleData {
    id: number;
    name: string;
    period_start_date: string;
    status: string;
}

interface AssignedShiftTemplate {
    id: number;
    name: string;
    required_staff_count: number;
}

interface User {
    id: number;
    name: string;
}

interface MonthDay {
    date: string;
    day_number: number;
    is_sunday: boolean;
    is_saturday: boolean;
    is_holiday: boolean;
}

interface EditProps {
    schedule: ScheduleData;
    assignedShiftTemplates: AssignedShiftTemplate[];
    users: User[];
    initialAssignments: Record<string, number | null>;
    monthDays: MonthDay[];
    errors: Record<string, string>;
}

const props = defineProps<EditProps>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Panel nawigacyjny',
        href: '/dashboard',
    },
    {
        title: 'Grafiki Pracy',
        href: '/schedules',
    },
    {
        title: `Edytuj: ${props.schedule.name}`,
        href: `/schedules/${props.schedule.id}/edit`,
    },
];

const form = useForm({
    _method: 'put',
    name: props.schedule.name,
    period_start_date: props.schedule.period_start_date,
    status: props.schedule.status,
    assignments: { ...props.initialAssignments } as Record<string, number | null>,
});

const localValidationErrors = ref<Record<string, string | null>>({});

const weeks = computed(() => {
    const weekChunks: MonthDay[][] = [];
    for (let i = 0; i < props.monthDays.length; i += 7) {
        weekChunks.push(props.monthDays.slice(i, i + 7));
    }
    return weekChunks;
});

const getUserFullName = (user: User) => {
    return user.name;
};

const handleAssignmentChange = (
    shiftTemplateId: number,
    date: string,
    position: number,
    valueFromSelect: string | null | number | bigint | Record<string, any>
) => {
    const key = `${shiftTemplateId}_${date}_${position}`;
    const stringValue = valueFromSelect as string;
    const selectedUserId = stringValue === "unassigned" ? null : parseInt(stringValue);

    form.assignments[key] = selectedUserId;

    // --- Logika walidacji duplikatów w tej samej zmianie, tego samego dnia ---
    // Najpierw usuwamy błąd dla bieżącej komórki
    localValidationErrors.value[key] = null;
    const currentShiftTemplate = props.assignedShiftTemplates.find(st => st.id === shiftTemplateId);

    if (selectedUserId !== null) { 
        

        if (currentShiftTemplate) {
            for (let i = 1; i <= currentShiftTemplate.required_staff_count; i++) {
                if (i === position) {
                    continue;
                }

                const otherKey = `${shiftTemplateId}_${date}_${i}`;
                const otherUserId = form.assignments[otherKey];

                if (otherUserId === selectedUserId) {
                    const errorMessage = `Pracownik "${getUserFullName(props.users.find(u => u.id === selectedUserId)!)}" jest już przypisany do tej zmiany.`;
                    localValidationErrors.value[key] = errorMessage;
                    localValidationErrors.value[otherKey] = errorMessage;
                    break;
                }
            }
        }
    }

    if (currentShiftTemplate) {
         for (let i = 1; i <= currentShiftTemplate.required_staff_count; i++) {
            const currentKeyToCheck = `${shiftTemplateId}_${date}_${i}`;
            const currentUserIdToCheck = form.assignments[currentKeyToCheck];
            
            localValidationErrors.value[currentKeyToCheck] = null;

            if (currentUserIdToCheck !== null) {
                for (let j = 1; j <= currentShiftTemplate.required_staff_count; j++) {
                    if (i === j) continue; 
                    
                    const otherKeyToCheck = `${shiftTemplateId}_${date}_${j}`;
                    const otherUserIdToCheck = form.assignments[otherKeyToCheck];

                    if (currentUserIdToCheck === otherUserIdToCheck) {
                        const errorMessage = `Pracownik "${getUserFullName(props.users.find(u => u.id === currentUserIdToCheck)!)}" jest już przypisany do tej zmiany.`;
                        localValidationErrors.value[currentKeyToCheck] = errorMessage;
                        localValidationErrors.value[otherKeyToCheck] = errorMessage;
                    }
                }
            }
        }
    }
};


const prepareAssignmentsForSubmit = () => {
    for (const key in localValidationErrors.value) {
        if (localValidationErrors.value[key]) {
            toast.error('Popraw błędy przypisania pracowników przed zapisaniem.');
            return null;
        }
    }

    const submittedAssignments: {
        shift_template_id: number;
        user_id: number | null;
        assignment_date: string;
        position: number;
    }[] = [];

    props.assignedShiftTemplates.forEach(shiftTemplate => {
        props.monthDays.forEach(day => {
            for (let i = 1; i <= shiftTemplate.required_staff_count; i++) {
                const key = `${shiftTemplate.id}_${day.date}_${i}`;
                const userId = form.assignments[key];

                if (userId !== null && userId !== undefined) {
                    submittedAssignments.push({
                        shift_template_id: shiftTemplate.id,
                        user_id: userId,
                        assignment_date: day.date,
                        position: i,
                    });
                }
            }
        });
    });
    return submittedAssignments;
};

const submit = () => {
    const tempForm = useForm({
        _method: 'put',
        name: form.name,
        period_start_date: form.period_start_date,
        status: form.status,
        assignments: prepareAssignmentsForSubmit(),
    });

    tempForm.post(route('schedules.update', props.schedule.id), {
        onSuccess: () => {
            toast.success('Grafik pracy został pomyślnie zaktualizowany.');
        },
        onError: (errors) => {
            if (Object.keys(errors).length === 0) {
                toast.error('Wystąpił nieoczekiwany błąd podczas aktualizacji grafiku pracy.');
            } else {
                toast.error('Wystąpiły błędy walidacji. Sprawdź formularz.');
                form.errors = errors;
            }
        },
    });
};

const getAssignmentError = (shiftTemplateId: number, date: string, position: number) => {
    const backendErrors = form.errors as Record<string, string>;
    const localErrorKey = `${shiftTemplateId}_${date}_${position}`;

    if (localValidationErrors.value[localErrorKey]) {
        return localValidationErrors.value[localErrorKey];
    }

    for (const errorKey in backendErrors) {
        if (errorKey.startsWith('assignments.') &&
            errorKey.includes(`${shiftTemplateId}_${date}_${position}`) &&
            errorKey.endsWith('.user_id')) {
            return backendErrors[errorKey];
        }
    }
    return null;
}
</script>

<template>

    <Head :title="`Edytuj Grafik: ${schedule.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <Card class="w-full">
                <CardHeader>
                    <CardTitle>Edytuj Grafik Pracy: {{ schedule.name }}</CardTitle>
                    <CardDescription>
                        Uzupełnij lub zmień przypisania pracowników do zmian.
                        Miesiąc oraz przypisane zmiany nie są edytowalne.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label for="name">Nazwa Grafiku</Label>
                                <Input id="name" v-model="form.name" type="text" disabled />
                                <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.name }}
                                </div>
                            </div>
                            <div>
                                <Label for="period_start_date">Miesiąc Grafiku</Label>
                                <Input id="period_start_date"
                                    :model-value="new Date(schedule.period_start_date).toLocaleDateString('pl-PL', { month: 'long', year: 'numeric' })"
                                    type="text" disabled />
                            </div>
                        </div>

                        <div v-for="(week, weekIndex) in weeks" :key="weekIndex" class="mb-8">
                            <h4 class="text-lg font-semibold mb-4">Tydzień {{ weekIndex + 1 }}</h4>
                            <div class="overflow-x-auto">
                                <Table class="min-w-full border-collapse">
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead class="w-[150px] sticky left-0 bg-white dark:bg-gray-800 z-10">
                                                Zmiana / Pracownik</TableHead>
                                            <TableHead v-for="day in week" :key="day.date" :class="cn(
                                                'text-center px-2 py-1 min-w-[120px]',
                                                { 'bg-red-200 dark:bg-red-800': day.is_sunday || day.is_holiday },
                                                { 'bg-yellow-200 dark:bg-yellow-700': day.is_saturday }
                                            )">
                                                <div class="font-bold">{{ day.day_number }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ new Date(day.date).toLocaleDateString('pl-PL', {
                                                        weekday: 'short'
                                                    }) }}
                                                </div>
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <template v-for="(shiftTemplate, shiftIndex) in props.assignedShiftTemplates"
                                            :key="shiftTemplate.id">
                                            <TableRow v-for="pos in shiftTemplate.required_staff_count"
                                                :key="`${shiftTemplate.id}-${pos}`" :class="cn(
                                                    'relative',
                                                    { 'bg-blue-50 dark:bg-blue-950': shiftIndex % 2 === 0 },
                                                    { 'bg-green-50 dark:bg-green-950': shiftIndex % 2 !== 0 }
                                                )">
                                                <TableCell class="sticky left-0 bg-white dark:bg-gray-800 z-10 p-2">
                                                    <div class="font-medium text-nowrap">
                                                        {{ shiftTemplate.name }} (P{{ pos }})
                                                    </div>
                                                </TableCell>
                                                <TableCell v-for="day in week" :key="`${day.date}-${pos}`" :class="cn(
                                                    'text-center px-1 py-1 border-l border-r',
                                                    { 'bg-red-100 dark:bg-red-900': day.is_sunday || day.is_holiday },
                                                    { 'bg-yellow-100 dark:bg-yellow-900': day.is_saturday },
                                                    // Dodajemy klasę border-red-500, jeśli jest błąd lokalny LUB backendowy
                                                    { 'border-red-500': getAssignmentError(shiftTemplate.id, day.date, pos) }
                                                )">
                                                    <Select
                                                        :model-value="(
                                                            form.assignments[`${shiftTemplate.id}_${day.date}_${pos}`] === null ? '' : form.assignments[`${shiftTemplate.id}_${day.date}_${pos}`]?.toString() || ''
                                                        )"
                                                        @update:model-value="(value) => handleAssignmentChange(shiftTemplate.id, day.date, pos, value)"
                                                        class="w-full"
                                                    >
                                                        <SelectTrigger class="w-full">
                                                            <SelectValue :placeholder="`${pos}. pracownik`" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value="unassigned">-- Nieprzypisany --
                                                            </SelectItem>
                                                            <SelectItem v-for="user in users" :key="user.id"
                                                                :value="user.id.toString()"
                                                            >
                                                                {{ getUserFullName(user) }}
                                                            </SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                    <div v-if="getAssignmentError(shiftTemplate.id, day.date, pos)"
                                                        class="text-red-500 text-xs mt-1">
                                                        {{ getAssignmentError(shiftTemplate.id, day.date, pos) }}
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
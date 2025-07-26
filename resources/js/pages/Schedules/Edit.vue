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

// Nowy interfejs dla preferencji
interface UserPreferences {
    [date: string]: boolean; // date string (YYYY-MM-DD) maps to availability (true/false)
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
    initialAssignments: Record<string, number | null>; // "shiftTemplateId_date_position" -> user_id
    monthDays: {
        date: string;
        day_number: number;
        is_sunday: boolean;
        is_saturday: boolean;
        is_holiday: boolean;
    }[];
    preferences: Record<string, UserPreferences>; // userId -> { date -> availability }
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

// Zmieniamy typ form.assignments, aby zawsze trzymał string lub null dla id użytkownika
// Konwersja na number nastąpi w funkcji transform() przed wysłaniem
const form = useForm({
    name: props.schedule.name,
    period_start_date: props.schedule.period_start_date,
    status: props.schedule.status,
    // Konwersja initialAssignments na stringi dla kluczy i wartości user_id
    assignments: Object.fromEntries(
        Object.entries(props.initialAssignments).map(([key, value]) => [key, value !== null ? String(value) : null])
    ) as Record<string, string | null>, // Jawne rzutowanie typu
});

// Lokalny stan błędów walidacji dla przypisań, jeśli potrzebne
const localValidationErrors = ref<Record<string, string | null>>({});

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

// Funkcja do pobierania przypisanego użytkownika dla danego pola
// Zwraca string (ID użytkownika) lub null
const getAssignedUser = (shiftTemplateId: number, date: string, position: number): string | null => {
    const key = `${shiftTemplateId}_${date}_${position}`;
    // Upewniamy się, że zwracamy string lub null
    const assignedId = form.assignments[key];
    // Jeśli wartość jest null lub undefined, zwracamy "-1" dla opcji "Brak"
    return assignedId !== undefined && assignedId !== null ? String(assignedId) : "-1";
};

// Funkcja do ustawiania przypisanego użytkownika
// Przyjmuje string (ID użytkownika) lub null
const setAssignment = (shiftTemplateId: number, date: string, position: number, userId: string | null) => {
    const key = `${shiftTemplateId}_${date}_${position}`;
    // Sprawdzamy, czy użytkownik wybrał opcję "Brak" (teraz to "-1")
    if (userId === "-1" || userId === null || userId === '') {
        const newAssignments = { ...form.assignments };
        delete newAssignments[key];
        form.assignments = newAssignments;
    } else {
        // userId jest już stringiem, więc przypisujemy bezpośrednio
        form.assignments = {
            ...form.assignments,
            [key]: userId, // Przypisujemy string
        };
    }
    // Wyczyszczenie błędu walidacji dla tej komórki po zmianie
    localValidationErrors.value[key] = null;
};

// Funkcja do uzyskania błędu walidacji dla danego pola
const getAssignmentError = (shiftTemplateId: number, date: string, position: number): string | null => {
    const key = `assignments.${shiftTemplateId}_${date}_${position}`;
    // Bezpieczny dostęp do błędów
    return form.errors[key as keyof typeof form.errors] || localValidationErrors.value[key];
};

// NOWA FUNKCJA: Pobieranie dostępności pracownika na dany dzień
const getUserAvailability = (userId: number, date: string): boolean | null => {
    // Sprawdź, czy istnieją preferencje dla tego użytkownika
    if (props.preferences[userId]) {
        // Sprawdź, czy istnieje preferencja dla konkretnego dnia
        const availability = props.preferences[userId][date];
        // Upewnij się, że wartość jest faktycznie boolean lub null
        if (typeof availability === 'boolean') {
            return availability;
        }
    }
    return null; // Brak preferencji dla tego użytkownika/dnia
};

// Funkcja pomocnicza do klasy SelectItem
const getAvailabilityClass = (userId: number, date: string) => {
    const availability = getUserAvailability(userId, date);
    if (availability === true) {
        return 'text-green-600 font-semibold'; // Zielony dla dostępnych
    } else if (availability === false) {
        return 'text-red-600 font-semibold'; // Czerwony dla niedostępnych
    }
    return ''; // Brak koloru dla braku preferencji
};

const submit = () => {
    // Używamy form.transform do przekształcenia danych przed wysłaniem
    form.transform((data) => {
        const transformedAssignments: Record<string, number | null> = {};
        for (const key in data.assignments) {
            const userIdAsString = data.assignments[key];
            // Konwersja na number lub null, "-1" oznacza null
            transformedAssignments[key] = userIdAsString !== null && userIdAsString !== "-1" ? parseInt(userIdAsString) : null;
        }
        // Zwracamy cały obiekt danych formularza z przekształconymi przypisaniami
        return {
            ...data,
            assignments: transformedAssignments,
        };
    }).put(route('schedules.update', props.schedule.id), {
        onSuccess: () => {
            toast.success('Grafik pracy został pomyślnie zaktualizowany.');
        },
        onError: (errors) => {
            console.error('Błędy walidacji:', errors);
            // Mapowanie błędów z backendu do localValidationErrors
            for (const key in errors) {
                if (key.startsWith('assignments.')) {
                    // Tutaj również musimy być bezpieczni z dostępem
                    localValidationErrors.value[key.substring(12)] = (errors as Record<string, string>)[key];
                } else {
                    toast.error((errors as Record<string, string>)[key]);
                }
            }
            toast.error('Wystąpiły błędy podczas aktualizacji grafiku.');
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
                        <div class="grid grid-cols-1 gap-4 mb-6">
                            <div>
                                <Label for="name">Nazwa Grafiku</Label>
                                <Input id="name" v-model="form.name" type="text" disabled />
                                <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                            </div>
                            <div>
                                <Label for="period_start_date">Miesiąc (od)</Label>
                                <Input id="period_start_date" v-model="form.period_start_date" type="date" disabled />
                                <div v-if="form.errors.period_start_date" class="text-red-500 text-xs mt-1">{{ form.errors.period_start_date }}</div>
                            </div>
                        </div>

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
                                                        <Select
                                                            :model-value="getAssignedUser(shiftTemplate.id, day.date, pos)"
                                                            @update:model-value="val => setAssignment(shiftTemplate.id, day.date, pos, val)"
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
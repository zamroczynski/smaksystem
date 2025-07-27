<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { toast } from 'vue-sonner';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { type DateRange } from 'reka-ui'

import { type Ref, ref, watch } from 'vue';
import { Calendar as CalendarIcon } from 'lucide-vue-next';
import { DateFormatter, type DateValue } from '@internationalized/date';
import { RangeCalendar } from '@/components/ui/range-calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { CalendarDate } from '@internationalized/date';
import { getLocalTimeZone } from "@internationalized/date";

interface Preference {
    id: number;
    date_from: string;
    date_to: string;
    description: string | undefined;
    availability: boolean;
}

const props = defineProps<{
    preference: Preference;
    breadcrumbs: BreadcrumbItem[]
}>();

const stringToCalendarDate = (dateString: string): CalendarDate => {
    const [year, month, day] = dateString.split('-').map(Number);
    return new CalendarDate(year, month, day);
};

const getTodayAsCalendarDate = (): CalendarDate => {
    const today = new Date();
    return new CalendarDate(today.getFullYear(), today.getMonth() + 1, today.getDate());
};

const form = useForm({
    date_from: null as string | null,
    date_to: null as string | null,
    description: props.preference.description,
    availability: props.preference.availability,
});

const dateRange = ref({
    start: stringToCalendarDate(props.preference.date_from),
    end: stringToCalendarDate(props.preference.date_to),
}) as Ref<DateRange>;

watch(dateRange, (newRange) => {
    form.date_from = newRange?.start ? newRange.start.toString() : null;
    form.date_to = newRange?.end ? newRange.end.toString() : null;
}, { deep: true });

const formatter = new DateFormatter('pl-PL', {
    dateStyle: 'medium',
});

const submit = () => {
    if (!form.date_from || !form.date_to) {
        toast.error('Proszę wybrać zakres dat.');
        return;
    }

    form.put(route('preferences.update', props.preference.id), {
        onSuccess: () => {
            toast.success('Preferencja grafiku została pomyślnie zaktualizowana.');
        },
        onError: (errors) => {
            if (Object.keys(errors).length > 0) {
                const firstErrorKey = Object.keys(errors)[0];
                toast.error(errors[firstErrorKey]);
            } else {
                toast.error('Wystąpił nieznany błąd podczas aktualizacji preferencji.');
            }
        },
    });
};
</script>

<template>

    <Head :title="`Edytuj Preferencję: ${props.preference.description || props.preference.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <Card class="w-full max-w-2xl mx-auto">
                <CardHeader>
                    <CardTitle>Edytuj Preferencję Grafiku</CardTitle>
                    <CardDescription>
                        Zmodyfikuj swoje preferencje dotyczące grafiku pracy.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label>Dyspozycja na wybrane dni <span class="text-red-500">*</span></Label>
                            <RadioGroup v-model="form.availability" class="flex gap-4 mt-2">
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem id="availability-edit-true" :value="true" />
                                    <Label for="availability-edit-true">Chcę przyjść do pracy</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem id="availability-edit-false" :value="false" />
                                    <Label for="availability-edit-false">Nie mogę przyjść do pracy</Label>
                                </div>
                            </RadioGroup>
                            <div v-if="form.errors.availability" class="text-red-500 text-sm mt-1">
                                {{ form.errors.availability }}
                            </div>
                        </div>
                        <div>
                            <Label for="date_range"
                                :class="{ 'text-red-500': form.errors['date_from'] || form.errors['date_to'] }">
                                Okres preferencji <span class="text-red-500">*</span>
                            </Label>
                            <Popover>
                                <PopoverTrigger as-child>
                                    <Button variant="outline" :class="cn(
                                        'w-full justify-start text-left font-normal',
                                        !dateRange && 'text-muted-foreground',
                                        { 'border-red-500': form.errors.date_from || form.errors.date_to }
                                    )">
                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                        <template v-if="dateRange?.start">
                                            <template v-if="dateRange.end">
                                                {{ formatter.format(dateRange.start.toDate(getLocalTimeZone())) }} -
                                                {{ formatter.format(dateRange.end.toDate(getLocalTimeZone())) }}
                                            </template>
                                            <template v-else>
                                                {{ formatter.format(dateRange.start.toDate(getLocalTimeZone())) }}
                                            </template>
                                        </template>
                                        <template v-else>
                                            Wybierz zakres dat
                                        </template>
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-auto p-0">
                                    <RangeCalendar v-model="dateRange" :number-of-months="2" initial-focus
                                        :min-value="getTodayAsCalendarDate()" weekday-format="short" />
                                </PopoverContent>
                            </Popover>
                            <div v-if="form.errors['date_from']" class="text-red-500 text-sm mt-1">
                                {{ form.errors['date_from'] }}
                            </div>
                            <div v-if="form.errors['date_to']" class="text-red-500 text-sm mt-1">
                                {{ form.errors['date_to'] }}
                            </div>
                        </div>

                        <div>
                            <Label for="description">Opis (opcjonalnie)</Label>
                            <Textarea id="description" v-model="form.description"
                                placeholder="Wprowadź opis preferencji (np. Preferuję pracować rano, Nieobecność)"
                                :class="{ 'border-red-500': form.errors.description }" />
                            <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">
                                {{ form.errors.description }}
                            </div>
                        </div>

                        <div class="flex justify-end gap-2">
                            <Button variant="outline" type="button" as-child>
                                <Link :href="route('preferences.index')">Anuluj</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Zapisywanie...' : 'Zaktualizuj Preferencję' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
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


const getTodayAsCalendarDate = (): CalendarDate => {
    const today = new Date();
    return new CalendarDate(today.getFullYear(), today.getMonth() + 1, today.getDate());
};

const form = useForm({
    date_from: null as string | null,
    date_to: null as string | null,
    description: '',
});

// Reaktywny stan dla wybranego zakresu dat w kalendarzu
const dateRange = ref({
    start: getTodayAsCalendarDate(),
    end: getTodayAsCalendarDate().add({ days: 20 }),
}) as Ref<DateRange>

watch(dateRange, (newRange) => {
    form.date_from = newRange?.start ? newRange.start.toString() : null;
    form.date_to = newRange?.end ? newRange.end.toString() : null;
}, { deep: true });

// Formatowanie daty do wyświetlania
const formatter = new DateFormatter('pl-PL', {
    dateStyle: 'medium',
});

const submit = () => {
    if (!form.date_from || !form.date_to) {
        toast.error('Proszę wybrać zakres dat.');
        return;
    }

    form.post(route('preferences.store'), {
        onSuccess: () => {
            toast.success('Preferencja grafiku została pomyślnie dodana.');
            form.reset();
            dateRange.value = {
                start: getTodayAsCalendarDate(),
                end: getTodayAsCalendarDate().add({ days: 1 }),
            };
        },
        onError: (errors) => {
            if (Object.keys(errors).length > 0) {
                const firstErrorKey = Object.keys(errors)[0];
                toast.error(errors[firstErrorKey]);
            } else {
                toast.error('Wystąpił nieznany błąd podczas dodawania preferencji.');
            }
        },
    });
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Panel nawigacyjny',
        href: '/dashboard',
    },
    {
        title: 'Moje Preferencje',
        href: '/preferences',
    },
    {
        title: 'Dodaj Preferencję',
        href: '/preferences/create',
    },
];
</script>

<template>

    <Head title="Dodaj Preferencję" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <Card class="w-full max-w-2xl mx-auto">
                <CardHeader>
                    <CardTitle>Dodaj Nową Preferencję Grafiku</CardTitle>
                    <CardDescription>
                        Uzupełnij formularz, aby dodać swoje preferencje dotyczące grafiku pracy.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
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
                                        :min-value="getTodayAsCalendarDate()"
                                        weekday-format="short" />
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
                                {{ form.processing ? 'Zapisywanie...' : 'Zapisz Preferencję' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
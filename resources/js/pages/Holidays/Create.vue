<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';
import { type HolidayCreateProps } from '@/types/holiday';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'vue-sonner';

const props = defineProps<HolidayCreateProps>();

const holidayType = ref<'single' | 'fixed' | 'movable' | null>(null);

const form = useForm({
    name: '',
    date: null as string | null,
    day_month: null as string | null,
    calculation_rule: null as {
        base_type: 'event' | 'holiday';
        base_event: 'easter' | null;
        base_holiday_id: number | null;
        offset: number;
    } | null,
});

watch(holidayType, (newType) => {
    form.clearErrors();
    form.date = null;
    form.day_month = null;
    form.calculation_rule = null;

    if (newType === 'movable') {
        form.calculation_rule = {
            base_type: 'event',
            base_event: 'easter',
            base_holiday_id: null,
            offset: 0,
        };
    }
});

const handleBaseHolidayChange = (value: string | number) => {
    if (form.calculation_rule) {
        if (value === 'easter') {
            form.calculation_rule.base_type = 'event';
            form.calculation_rule.base_event = 'easter';
            form.calculation_rule.base_holiday_id = null;
        } else {
            form.calculation_rule.base_type = 'holiday';
            form.calculation_rule.base_event = null;
            form.calculation_rule.base_holiday_id = Number(value);
        }
    }
};

const submit = () => {
    form.post(route('holidays.store'), {
        onSuccess: () => {
            toast.success('Dzień wolny został pomyślnie dodany.');
            form.reset();
            holidayType.value = null;
        },
        onError: (errors) => {
            if (Object.keys(errors).length > 0) {
                toast.error('Wystąpiły błędy walidacji. Sprawdź formularz.');
            } else {
                toast.error('Wystąpił nieoczekiwany błąd.');
            }
        },
    });
};
</script>

<template>
    <Head title="Dodaj Dzień Wolny" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Dodaj Dzień Wolny</h3>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <Label for="name">Nazwa <span class="text-red-500">*</span></Label>
                        <Input id="name" type="text" v-model="form.name" required />
                        <p v-if="form.errors.name" class="text-sm text-red-500 mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <Label for="holiday_type">Typ dnia wolnego <span class="text-red-500">*</span></Label>
                        <Select v-model="holidayType">
                            <SelectTrigger>
                                <SelectValue placeholder="Wybierz typ..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="single">Jednorazowy</SelectItem>
                                <SelectItem value="fixed">Stały, coroczny</SelectItem>
                                <SelectItem value="movable">Ruchomy, obliczalny (np. Wielkanoc)</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div v-if="holidayType === 'single'">
                        <Label for="date">Konkretna data <span class="text-red-500">*</span></Label>
                        <Input id="date" type="date" v-model="form.date" required />
                        <p v-if="form.errors.date" class="text-sm text-red-500 mt-1">{{ form.errors.date }}</p>
                    </div>

                    <div v-if="holidayType === 'fixed'">
                        <Label for="day_month">Dzień i miesiąc <span class="text-red-500">*</span></Label>
                        <Input id="day_month" type="text" v-model="form.day_month" placeholder="MM-DD (np. 01-01 dla Nowego Roku)" required />
                        <p v-if="form.errors.day_month" class="text-sm text-red-500 mt-1">{{ form.errors.day_month }}</p>
                    </div>

                    <div v-if="holidayType === 'movable' && form.calculation_rule" class="space-y-4 rounded-md border p-4">
                        <h4 class="font-medium text-sm text-gray-600 dark:text-gray-300">Reguła obliczeniowa</h4>
                        <div>
                            <Label>Święto bazowe</Label>
                            <Select :model-value="form.calculation_rule.base_event || form.calculation_rule.base_holiday_id" @update:model-value="handleBaseHolidayChange">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="easter">Wielkanoc (Algorytm)</SelectItem>
                                    <SelectItem v-for="base in props.baseHolidays" :key="base.id" :value="base.id">
                                        {{ base.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <Label for="offset">Przesunięcie (w dniach)</Label>
                            <Input id="offset" type="number" v-model.number="form.calculation_rule.offset" />
                            <p class="text-xs text-gray-500 mt-1">
                                Użyj wartości dodatniej (np. 14) lub ujemnej. 0 oznacza ten sam dzień co święto bazowe.
                            </p>
                            <p v-if="form.errors.calculation_rule" class="text-sm text-red-500 mt-1">{{ form.errors.calculation_rule }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <Button type="submit" :disabled="form.processing">Dodaj Dzień Wolny</Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
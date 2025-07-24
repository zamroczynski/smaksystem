<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { toast } from 'vue-sonner';
import { ref, watch, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

interface ShiftTemplateProps {
    id: number;
    name: string;
    time_from: string;
    time_to: string;
    duration_hours: number;
    required_staff_count: number;
}

const props = defineProps<{
    shiftTemplate: ShiftTemplateProps;
}>();

const form = useForm({
    name: props.shiftTemplate.name,
    time_from: props.shiftTemplate.time_from,
    time_to: props.shiftTemplate.time_to,
    duration_hours: props.shiftTemplate.duration_hours,
    required_staff_count: props.shiftTemplate.required_staff_count,
});

form.transform((data) => ({
    ...data,
    duration_hours: parseFloat(data.duration_hours.toFixed(2)),
}));

const calculateDuration = () => {
    const timeFrom = form.time_from;
    const timeTo = form.time_to;

    if (!timeFrom || !timeTo) {
        form.duration_hours = 0;
        return;
    }

    const [fromHours, fromMinutes] = timeFrom.split(':').map(Number);
    const [toHours, toMinutes] = timeTo.split(':').map(Number);

    let totalFromMinutes = fromHours * 60 + fromMinutes;
    let totalToMinutes = toHours * 60 + toMinutes;

    let durationInMinutes;

    if (totalToMinutes < totalFromMinutes) {
        durationInMinutes = (totalToMinutes + 1440) - totalFromMinutes;
    } else {
        durationInMinutes = totalToMinutes - totalFromMinutes;
    }

    form.duration_hours = parseFloat((durationInMinutes / 60).toFixed(2));
};

watch([() => form.time_from, () => form.time_to], () => {
    calculateDuration();
}, { immediate: true }); 

const submit = () => {
    form.put(route('shift-templates.update', props.shiftTemplate.id), {
        onSuccess: () => {
            toast.success('Harmonogram zmian został pomyślnie zaktualizowany.');
        },
        onError: (errors) => {
            if (Object.keys(errors).length > 0) {
                const firstErrorKey = Object.keys(errors)[0];
                toast.error(errors[firstErrorKey]);
            } else {
                toast.error('Wystąpił nieznany błąd podczas aktualizacji harmonogramu zmian.');
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
        title: 'Harmonogramy Zmian',
        href: '/shift-templates',
    },
    {
        title: 'Edytuj Harmonogram Zmian',
        href: `/shift-templates/${props.shiftTemplate.id}/edit`,
    },
];
</script>

<template>
    <Head :title="`Edytuj Harmonogram Zmian: ${props.shiftTemplate.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <Card class="w-full max-w-2xl mx-auto">
                <CardHeader>
                    <CardTitle>Edytuj Harmonogram Zmian</CardTitle>
                    <CardDescription>
                        Zmień szczegóły istniejącego szablonu zmiany pracy.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="name" :class="{ 'text-red-500': form.errors.name }">Nazwa Zmiany <span class="text-red-500">*</span></Label>
                            <Input id="name" v-model="form.name" type="text"
                                placeholder="Np. Dniówka, Nocka, Poranna"
                                :class="{ 'border-red-500': form.errors.name }" />
                            <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <div>
                            <Label for="time_from" :class="{ 'text-red-500': form.errors.time_from }">Godzina Rozpoczęcia <span class="text-red-500">*</span></Label>
                            <Input id="time_from" v-model="form.time_from" type="time"
                                :class="{ 'border-red-500': form.errors.time_from }" />
                            <div v-if="form.errors.time_from" class="text-red-500 text-sm mt-1">
                                {{ form.errors.time_from }}
                            </div>
                        </div>

                        <div>
                            <Label for="time_to" :class="{ 'text-red-500': form.errors.time_to }">Godzina Zakończenia <span class="text-red-500">*</span></Label>
                            <Input id="time_to" v-model="form.time_to" type="time"
                                :class="{ 'border-red-500': form.errors.time_to }" />
                            <div v-if="form.errors.time_to" class="text-red-500 text-sm mt-1">
                                {{ form.errors.time_to }}
                            </div>
                        </div>

                        <div>
                            <Label for="duration_hours" :class="{ 'text-red-500': form.errors.duration_hours }">Czas trwania (godziny)</Label>
                            <Input id="duration_hours" :value="form.duration_hours + 'h'" type="text" readonly
                                :class="{ 'border-red-500': form.errors.duration_hours }" />
                            <div v-if="form.errors.duration_hours" class="text-red-500 text-sm mt-1">
                                {{ form.errors.duration_hours }}
                            </div>
                        </div>

                        <div>
                            <Label for="required_staff_count" :class="{ 'text-red-500': form.errors.required_staff_count }">Wymagana liczba pracowników <span class="text-red-500">*</span></Label>
                            <Input id="required_staff_count" v-model="form.required_staff_count" type="number" min="0"
                                :class="{ 'border-red-500': form.errors.required_staff_count }" />
                            <div v-if="form.errors.required_staff_count" class="text-red-500 text-sm mt-1">
                                {{ form.errors.required_staff_count }}
                            </div>
                        </div>

                        <div class="flex justify-end gap-2">
                            <Button variant="outline" type="button" as-child>
                                <Link :href="route('shift-templates.index')">Anuluj</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Zapisywanie...' : 'Zapisz Zmianę' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
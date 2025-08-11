<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { toast } from 'vue-sonner';
import { ref, computed } from 'vue';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { format } from 'date-fns';
import { pl } from 'date-fns/locale';
import { CalendarDate, parseDate } from '@internationalized/date';
import { Checkbox } from '@/components/ui/checkbox';
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

interface ShiftTemplate {
    id: number;
    name: string;
}

interface CreateProps {
    activeShiftTemplates: ShiftTemplate[];
    errors: Record<string, string>;
    breadcrumbs: BreadcrumbItem[];
}

const props = defineProps<CreateProps>();

const form = useForm({
    name: '',
    period_start_date: format(new Date(), 'yyyy-MM-01'),
    status: 'draft',
    selected_shift_templates: [] as number[],
});

const isConfirmSaveDialogOpen = ref(false);

const submit = () => {
    if (form.name === '' || form.period_start_date === '') {
        toast.error('Proszę wypełnić wymagane pola.');
        return;
    }
    isConfirmSaveDialogOpen.value = true;
};

const saveScheduleConfirmed = () => {
    form.post(route('schedules.store'), {
        onSuccess: () => {
            toast.success('Grafik pracy został pomyślnie dodany.');
            form.reset();
            form.period_start_date = format(new Date(), 'yyyy-MM-01');
            isConfirmSaveDialogOpen.value = false;
        },
        onError: (errors) => {
            if (Object.keys(errors).length === 0) {
                toast.error('Wystąpił nieoczekiwany błąd podczas dodawania grafiku pracy.');
            } else {
                toast.error('Wystąpiły błędy walidacji. Sprawdź formularz.');
            }
            isConfirmSaveDialogOpen.value = false;
        },
    });
};

const selectedDate = computed({
    get: () => form.period_start_date ? parseDate(form.period_start_date) : undefined,
    set: (val: CalendarDate | undefined) => {
        if (val) {
            const jsDate = new Date(val.year, val.month - 1, 1);
            form.period_start_date = format(jsDate, 'yyyy-MM-dd');
        } else {
            form.period_start_date = '';
        }
    },
});

const hasShiftTemplatesError = () => {
    return Object.keys(form.errors).some(key => key.startsWith('selected_shift_templates.'));
};

const getFirstShiftTemplatesError = () => {
    for (const key in form.errors) {
        if (key.startsWith('selected_shift_templates.')) {
            return (form.errors as any)[key];
        }
    }
    if (form.errors.selected_shift_templates) {
        return form.errors.selected_shift_templates;
    }
    return null;
};

const handleShiftTemplateChange = (templateId: number, isChecked: boolean | 'indeterminate') => {
    if (isChecked === 'indeterminate') {
        return;
    }

    if (isChecked) {
        if (!form.selected_shift_templates.includes(templateId)) {
            form.selected_shift_templates.push(templateId);
        }
    } else {
        form.selected_shift_templates = form.selected_shift_templates.filter(id => id !== templateId);
    }
};
</script>

<template>

    <Head title="Dodaj Grafik Pracy" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <Card class="w-full max-w-2xl mx-auto">
                <CardHeader>
                    <CardTitle>Dodaj Nowy Grafik Pracy</CardTitle>
                    <CardDescription>
                        Uzupełnij szczegóły, aby zdefiniować nowy grafik pracy na dany okres.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="name" :class="{ 'text-red-500': form.errors.name }">Nazwa Grafiku <span
                                    class="text-red-500">*</span></Label>
                            <Input id="name" v-model="form.name" type="text" placeholder="Np. Grafik Luty 2025"
                                :class="{ 'border-red-500': form.errors.name }" />
                            <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <div>
                            <Label for="period_start_date"
                                :class="{ 'text-red-500': form.errors.period_start_date }">Okres od <span
                                    class="text-red-500">*</span></Label>
                            <Popover>
                                <PopoverTrigger as-child>
                                    <Button variant="outline" :class="[
                                        'w-full justify-start text-left font-normal',
                                        !selectedDate && 'text-muted-foreground',
                                        { 'border-red-500': form.errors.period_start_date }
                                    ]">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                            class="w-4 h-4 mr-2">
                                            <path fill-rule="evenodd"
                                                d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3A.75.75 0 0 1 18 3v1.5h.75a3 3 0 0 1 3 3v11.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3H6.75V3ZM4.5 7.5a1.5 1.5 0 0 0-1.5 1.5v10.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5V9a1.5 1.5 0 0 0-1.5-1.5H4.5Z"
                                                clip-rule="evenodd" />
                                        </svg>

                                        {{ selectedDate ? format(new Date(selectedDate.year, selectedDate.month - 1,
                                            selectedDate.day), 'PPP', { locale: pl }) : 'Wybierz datę' }}
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-auto p-0">
                                    <Calendar v-model="selectedDate" :initial-focus="true" mode="single" locale="pl" />
                                </PopoverContent>
                            </Popover>
                            <div v-if="form.errors.period_start_date" class="text-red-500 text-sm mt-1">
                                {{ form.errors.period_start_date }}
                            </div>
                        </div>
                        <div>
                            <Label
                                :class="{ 'text-red-500': hasShiftTemplatesError() || form.errors.selected_shift_templates }">
                                Przypisz harmonogramy zmian
                            </Label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                                <div v-for="template in props.activeShiftTemplates" :key="template.id"
                                    class="flex items-center space-x-2">
                                    <Checkbox :id="`shift-template-${template.id}`" :value="template.id"
                                        :model-value="form.selected_shift_templates.includes(template.id)"
                                        @update:model-value="(isChecked: boolean | 'indeterminate') => handleShiftTemplateChange(template.id, isChecked)" />
                                    <label :for="`shift-template-${template.id}`"
                                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                        {{ template.name }}
                                    </label>
                                </div>
                            </div>
                            <p v-if="hasShiftTemplatesError() || form.errors.selected_shift_templates"
                                class="text-sm text-red-500 mt-1">
                                {{ getFirstShiftTemplatesError() }}
                            </p>
                        </div>

                        <div class="flex justify-end gap-2">
                            <Button variant="outline" type="button" as-child>
                                <Link :href="route('schedules.index')">Anuluj</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Zapisywanie...' : 'Zapisz Grafik' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
        <AlertDialog :open="isConfirmSaveDialogOpen" @update:open="isConfirmSaveDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Potwierdź zapis grafiku pracy</AlertDialogTitle>
                    <AlertDialogDescription>
                        Upewnij się, że miesiąc oraz przypisane zmiany są poprawne.<br><br>
                        Po zapisaniu grafiku pracy nie będzie można edytować miesiąca ani przypisanych
                        zmian.
                        <br><br>
                        Czy chcesz kontynuować?
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="isConfirmSaveDialogOpen = false">Anuluj</AlertDialogCancel>
                    <AlertDialogAction @click="saveScheduleConfirmed"
                        class="bg-primary hover:bg-primary-dark">
                        Potwierdź i Zapisz
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
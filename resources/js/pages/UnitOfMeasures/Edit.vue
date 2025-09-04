<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type UnitOfMeasureEditProps } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<UnitOfMeasureEditProps>();

const form = useForm({
    name: props.unitOfMeasure.name,
    symbol: props.unitOfMeasure.symbol,
});

const submit = () => {
    form.put(route('unit-of-measures.update', props.unitOfMeasure.id), {
        onSuccess: () => {
            toast.success('Jednostka miary została pomyślnie zaktualizowana.');
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
    <Head :title="`Edycja: ${form.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Edytuj Jednostkę Miary: {{ form.name }}
                </h3>

                <form @submit.prevent="submit" class="space-y-4 max-w-md">
                     <div>
                        <Label for="name">Nazwa (np. Kilogram) <span class="text-red-500">*</span></Label>
                        <Input id="name" type="text" v-model="form.name" required />
                        <p v-if="form.errors.name" class="text-sm text-red-500 mt-1">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <Label for="symbol">Symbol (np. kg) <span class="text-red-500">*</span></Label>
                        <Input id="symbol" type="text" v-model="form.symbol" required />
                        <p v-if="form.errors.symbol" class="text-sm text-red-500 mt-1">{{ form.errors.symbol }}</p>
                    </div>

                    <div class="flex justify-end pt-4">
                        <Button type="submit" :disabled="form.processing">Zapisz Zmiany</Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type VatRateCreateProps } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<VatRateCreateProps>();

const form = useForm({
    name: '',
    rate: '',
});

const submit = () => {
    form.post(route('vat-rates.store'), {
        onSuccess: () => {
            toast.success('Stawka VAT została pomyślnie dodana.');
            form.reset();
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
    <Head title="Dodaj Stawkę VAT" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Dodaj Nową Stawkę VAT</h3>

                <form @submit.prevent="submit" class="space-y-4 max-w-md">
                    <div>
                        <Label for="name">Nazwa (np. Stawka podstawowa) <span class="text-red-500">*</span></Label>
                        <Input id="name" type="text" v-model="form.name" required />
                        <p v-if="form.errors.name" class="text-sm text-red-500 mt-1">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <Label for="rate">Stawka (%) <span class="text-red-500">*</span></Label>
                        <Input id="rate" type="number" step="0.01" v-model="form.rate" required placeholder="np. 23 lub 5.5"/>
                        <p v-if="form.errors.rate" class="text-sm text-red-500 mt-1">{{ form.errors.rate }}</p>
                    </div>

                    <div class="flex justify-end pt-4">
                        <Button type="submit" :disabled="form.processing">Dodaj Stawkę VAT</Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
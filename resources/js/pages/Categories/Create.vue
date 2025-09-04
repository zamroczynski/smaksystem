<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { CategoryCreateProps } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<CategoryCreateProps>();

const form = useForm({
    name: '',
});

const submit = () => {
    form.post(route('categories.store'), {
        onSuccess: () => {
            toast.success('Kategoria została pomyślnie dodana.');
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
    <Head title="Dodaj Kategorię" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Dodaj Nową Kategorię</h3>

                <form @submit.prevent="submit" class="space-y-4 max-w-md">
                    <div>
                        <Label for="name">Nazwa <span class="text-red-500">*</span></Label>
                        <Input id="name" type="text" v-model="form.name" required />
                        <p v-if="form.errors.name" class="text-sm text-red-500 mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div class="flex justify-end pt-4">
                        <Button type="submit" :disabled="form.processing">Dodaj Kategorię</Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
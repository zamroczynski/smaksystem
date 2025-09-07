<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { RecipeCreateProps, RecipeFormType } from '@/types/recipe';
import { Head, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import RecipeForm from '@/components/Recipes/RecipeForm.vue';

const props = defineProps<RecipeCreateProps>();

const form = useForm<RecipeFormType>({
    name: '',
    description: '',
    instructions: '',
    product_id: null,
    yield_quantity: '1',
    yield_unit_of_measure_id: null,
    is_active: true,
    ingredients: [{
        product_id: null,
        quantity: '1',
        unit_of_measure_id: null,
    }],
});

const recipeParams = {
    dishProducts: props.dishProducts,
    ingredientProducts: props.ingredientProducts,
    unitsOfMeasure: props.unitsOfMeasure,
};

const submit = () => {
    form.post(route('recipes.store'), {
        onSuccess: () => {
            toast.success('Receptura została pomyślnie dodana.');
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
    <Head title="Dodaj Recepturę" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Dodaj Nową Recepturę</h3>
                
                <RecipeForm :form="form" :recipe-params="recipeParams" @submit="submit" />

                <div class="flex justify-end pt-4 mt-4 border-t">
                    <Button @click="submit" :disabled="form.processing">Zapisz Recepturę</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
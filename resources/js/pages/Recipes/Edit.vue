<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { RecipeEditProps, RecipeFormType } from '@/types/recipe';
import { Head, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import RecipeForm from '@/components/Recipes/RecipeForm.vue';

const props = defineProps<RecipeEditProps>();

const form = useForm<RecipeFormType>({
    name: props.recipe.name,
    description: props.recipe.description ?? '',
    instructions: props.recipe.instructions ?? '',
    product_id: props.recipe.product_id,
    yield_quantity: props.recipe.yield_quantity,
    yield_unit_of_measure_id: props.recipe.yield_unit_of_measure_id,
    is_active: props.recipe.is_active,
    ingredients: props.recipe.ingredients.map(ing => ({
        ...ing,
        quantity: String(ing.quantity),
    })),
});

const recipeParams = {
    dishProducts: props.dishProducts,
    ingredientProducts: props.ingredientProducts,
    unitsOfMeasure: props.unitsOfMeasure,
};

const submit = () => {
    form.put(route('recipes.update', props.recipe.id), {
        onSuccess: () => {
            toast.success('Receptura została pomyślnie zaktualizowana.');
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
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Edytuj Recepturę: {{ form.name }}</h3>

                <RecipeForm :form="form" :recipe-params="recipeParams" @submit="submit" />

                <div class="flex justify-end pt-4 mt-4 border-t">
                    <Button @click="submit" :disabled="form.processing">Zapisz Zmiany</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
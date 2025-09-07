<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import type { RecipeFormType, RecipeCreateProps } from '@/types/recipe';
import type { InertiaForm } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { PlusCircleIcon, Trash2Icon } from 'lucide-vue-next';

const { form, recipeParams } = defineProps<{
    form: InertiaForm<RecipeFormType>; 
    recipeParams: Omit<RecipeCreateProps, 'flash' | 'breadcrumbs'>;
}>();

const emit = defineEmits(['submit']);

const addIngredient = () => {
    form.ingredients.push({
        product_id: null,
        quantity: 1,
        unit_of_measure_id: null,
    });
};

const removeIngredient = (index: number) => {
    form.ingredients.splice(index, 1);
};
</script>

<template>
     <form @submit.prevent="emit('submit')" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <Label for="name">Nazwa receptury <span class="text-red-500">*</span></Label>
                <Input id="name" type="text" v-model="form.name" />
                <p v-if="form.errors.name" class="text-sm text-red-500 mt-1">{{ form.errors.name }}</p>
            </div>

            <div>
                <Label for="product_id">Produkt wynikowy (danie) <span class="text-red-500">*</span></Label>
                    <Select v-model="form.product_id">
                    <SelectTrigger><SelectValue placeholder="Wybierz danie" /></SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="product in recipeParams.dishProducts" :key="product.id" :value="product.id">
                            {{ product.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.errors.product_id" class="text-sm text-red-500 mt-1">{{ form.errors.product_id }}</p>
            </div>
            
            <div>
                <Label for="description">Opis</Label>
                <Textarea id="description" v-model="form.description" />
                <p v-if="form.errors.description" class="text-sm text-red-500 mt-1">{{ form.errors.description }}</p>
            </div>

            <div>
                <Label for="instructions">Instrukcje przygotowania</Label>
                <Textarea id="instructions" v-model="form.instructions" />
                <p v-if="form.errors.instructions" class="text-sm text-red-500 mt-1">{{ form.errors.instructions }}</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <Label for="yield_quantity">Uzyskiwana ilość <span class="text-red-500">*</span></Label>
                    <Input id="yield_quantity" type="number" step="0.01" v-model="form.yield_quantity" />
                    <p v-if="form.errors.yield_quantity" class="text-sm text-red-500 mt-1">{{ form.errors.yield_quantity }}</p>
                </div>
                    <div>
                    <Label for="yield_unit_of_measure_id">Jednostka <span class="text-red-500">*</span></Label>
                    <Select v-model="form.yield_unit_of_measure_id">
                        <SelectTrigger><SelectValue placeholder="Wybierz jednostkę" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="unit in recipeParams.unitsOfMeasure" :key="unit.id" :value="unit.id">
                                {{ unit.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.yield_unit_of_measure_id" class="text-sm text-red-500 mt-1">{{ form.errors.yield_unit_of_measure_id }}</p>
                </div>
            </div>
        </div>

        <div class="space-y-4 pt-4 border-t">
                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100">Składniki</h4>
                <div v-for="(ingredient, index) in form.ingredients" :key="index" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end p-4 border rounded-md">
                    <div class="md:col-span-2">
                    <Label :for="`ing_product_${index}`">Składnik</Label>
                    <Select v-model="ingredient.product_id">
                        <SelectTrigger><SelectValue placeholder="Wybierz składnik" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="product in recipeParams.ingredientProducts" :key="product.id" :value="product.id">
                                {{ product.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors[`ingredients.${index}.product_id`]" class="text-sm text-red-500 mt-1">{{ form.errors[`ingredients.${index}.product_id`] }}</p>
                    </div>
                    <div>
                        <Label :for="`ing_quantity_${index}`">Ilość</Label>
                    <Input :id="`ing_quantity_${index}`" type="number" step="0.001" v-model="ingredient.quantity" />
                    <p v-if="form.errors[`ingredients.${index}.quantity`]" class="text-sm text-red-500 mt-1">{{ form.errors[`ingredients.${index}.quantity`] }}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="flex-grow">
                        <Label :for="`ing_unit_${index}`">Jednostka</Label>
                        <Select v-model="ingredient.unit_of_measure_id">
                            <SelectTrigger><SelectValue placeholder="Jednostka" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="unit in recipeParams.unitsOfMeasure" :key="unit.id" :value="unit.id">
                                    {{ unit.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors[`ingredients.${index}.unit_of_measure_id`]" class="text-sm text-red-500 mt-1">{{ form.errors[`ingredients.${index}.unit_of_measure_id`] }}</p>
                        </div>
                        <Button type="button" variant="destructive" size="icon" @click="removeIngredient(index)" class="shrink-0">
                            <Trash2Icon class="w-4 h-4"/>
                        </Button>
                    </div>
                </div>
                <Button type="button" variant="outline" @click="addIngredient" class="flex items-center gap-2">
                    <PlusCircleIcon class="w-4 h-4"/>
                    Dodaj składnik
                </Button>
        </div>
    </form>
</template>
 
import type { PaginatedData, PageProps } from '@/types';
import type { Recipe, UnitOfMeasure } from '@/types/models';
import type { FormDataConvertible } from '@inertiajs/core';

interface RecipeData extends PaginatedData {
    data: Recipe[];
}

export interface RecipeIndexProps extends PageProps {
    recipes: RecipeData;
    show_disabled: boolean;
    filter: string | null;
    sort_by: string | null;
    sort_direction: 'asc' | 'desc' | null;
}

export interface RecipeCreateProps extends PageProps, RecipeFormParams {}

export interface RecipeEditProps extends PageProps, RecipeFormParams {
    recipe: {
        id: number;
        name: string;
        description: string | null;
        instructions: string | null;
        product_id: number;
        yield_quantity: number;
        yield_unit_of_measure_id: number;
        is_active: boolean;
        ingredients: IngredientFormRow[];
    };
}

export interface IngredientFormRow {
    product_id: number | null;
    quantity: number | string;
    unit_of_measure_id: number | null;
}

export interface RecipeFormData {
    name: string;
    description: string;
    instructions: string;
    product_id: number | null;
    yield_quantity: number | string;
    yield_unit_of_measure_id: number | null;
    is_active: boolean;
    ingredients: IngredientFormRow[];
}

export type RecipeFormType = RecipeFormData & Record<string, FormDataConvertible | null>;

interface RecipeFormParams {
    dishProducts: { id: number; name: string }[];
    ingredientProducts: { id: number; name: string }[];
    unitsOfMeasure: UnitOfMeasure[];
}
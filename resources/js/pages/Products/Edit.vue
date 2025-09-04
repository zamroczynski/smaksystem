<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type ProductEditProps } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';
import ProductForm from '@/components/Products/ProductForm.vue';

const props = defineProps<ProductEditProps>();

const form = useForm({
    name: props.product.name,
    sku: props.product.sku,
    description: props.product.description,
    product_type_id: props.product.product_type_id,
    category_id: props.product.category_id,
    unit_of_measure_id: props.product.unit_of_measure_id,
    vat_rate_id: props.product.vat_rate_id,
    is_sellable: props.product.is_sellable,
    is_inventoried: props.product.is_inventoried,
    selling_price: props.product.selling_price,
    default_purchase_price: props.product.default_purchase_price,
});

const productParams = {
    productTypes: props.productTypes,
    categories: props.categories,
    unitsOfMeasure: props.unitsOfMeasure,
    vatRates: props.vatRates,
};

const submit = () => {
    form.put(route('products.update', props.product.id), {
        onSuccess: () => {
            toast.success('Produkt został pomyślnie zaktualizowany.');
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
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Edytuj Produkt: {{ form.name }}</h3>

                <ProductForm :form="form" :product-params="productParams" @submit="submit" />

                <div class="flex justify-end pt-4 mt-4 border-t">
                    <Button @click="submit" :disabled="form.processing">Zapisz Zmiany</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
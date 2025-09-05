<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { ProductCreateProps, ProductFormType } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';
import ProductForm from '@/components/Products/ProductForm.vue';

const props = defineProps<ProductCreateProps>();

const form = useForm<ProductFormType>({
    name: '',
    sku: '',
    description: '',
    product_type_id: null,
    category_id: null,
    unit_of_measure_id: null,
    vat_rate_id: null,
    is_sellable: false,
    is_inventoried: false,
    selling_price: '',
    default_purchase_price: '',
});

const productParams = {
    productTypes: props.productTypes,
    categories: props.categories,
    unitsOfMeasure: props.unitsOfMeasure,
    vatRates: props.vatRates,
};

const submit = () => {
    form.post(route('products.store'), {
        onSuccess: () => {
            toast.success('Produkt został pomyślnie dodany.');
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
    <Head title="Dodaj Produkt" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Dodaj Nowy Produkt</h3>
                
                <ProductForm :form="form" :product-params="productParams" @submit="submit" />

                <div class="flex justify-end pt-4 mt-4 border-t">
                    <Button @click="submit" :disabled="form.processing">Dodaj Produkt</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
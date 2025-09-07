<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { ProductIndexProps } from '@/types';
import type { Product } from '@/types/models';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch, h, defineAsyncComponent } from 'vue';
import { ColumnDef } from '@tanstack/vue-table';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
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

const DataTable = defineAsyncComponent(() => import('@/components/DataTable.vue'));

const props = defineProps<ProductIndexProps>();

const currentPage = ref(props.products.current_page);
const currentGlobalFilter = ref(props.filter);
const showDisabled = ref(props.show_disabled);

const form = useForm({});
const isAlertDialogOpen = ref(false);
const productToDeactivate = ref<Pick<Product, 'id' | 'name'> | null>(null);

const confirmDeactivate = (product: Pick<Product, 'id' | 'name'>) => {
    productToDeactivate.value = product;
    isAlertDialogOpen.value = true;
};

const deactivateProductConfirmed = () => {
    if (productToDeactivate.value) {
        form.delete(route('products.destroy', productToDeactivate.value.id), {
            onSuccess: () => {
                toast.success('Produkt został pomyślnie dezaktywowany.');
                isAlertDialogOpen.value = false;
                productToDeactivate.value = null;
                fetchTableData();
            },
            onError: () => toast.error(props.flash?.error || 'Wystąpił błąd podczas dezaktywacji.'),
        });
    }
};

const restoreProduct = (productId: number) => {
    router.post(route('products.restore', productId), {}, {
        onSuccess: () => {
            toast.success('Produkt został pomyślnie przywrócony.');
            fetchTableData();
        },
        onError: () => toast.error('Wystąpił błąd podczas przywracania.'),
    });
};

watch(showDisabled, (newValue) => {
    router.get(route('products.index', { show_disabled: newValue }), {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['products', 'show_disabled'],
    });
});

const fetchTableData = () => {
    router.get(
        route('products.index'),
        {
            page: currentPage.value,
            filter: currentGlobalFilter.value,
            show_disabled: showDisabled.value,
            sort: props.sort_by,
            direction: props.sort_direction,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['products', 'filter', 'show_disabled', 'sort_by', 'sort_direction'],
        }
    );
};

const handlePageUpdate = (newPage: number) => {
    currentPage.value = newPage;
    fetchTableData();
};

const handleFilterUpdate = (newFilter: string) => {
    currentGlobalFilter.value = newFilter;
    currentPage.value = 1;
    fetchTableData();
};

const handleSortUpdate = (sortData: { column: string, direction: string }) => {
    router.get(
        route('products.index'),
        {
            page: 1,
            filter: currentGlobalFilter.value,
            show_disabled: showDisabled.value,
            sort: sortData.column,
            direction: sortData.direction,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['products', 'filter', 'show_disabled', 'sort_by', 'sort_direction'],
        }
    );
};

const columns: ColumnDef<Product>[] = [
    { accessorKey: 'name', header: 'Nazwa', enableSorting: true },
    { accessorKey: 'sku', header: 'SKU', enableSorting: true },
    { accessorKey: 'category_name', header: 'Kategoria', enableSorting: true, id: 'category' },
    { 
        accessorKey: 'selling_price', 
        header: 'Cena sprzedaży',
        cell: ({ row }) => {
            const price = row.original.selling_price;
            if (price === null) return h('span', { class: 'text-gray-400' }, 'Brak');
            return h('span', price.toLocaleString('pl-PL', { style: 'currency', currency: 'PLN' }));
        },
        enableSorting: false,
     },
    {
        accessorKey: 'deleted_at',
        header: 'Status',
        cell: ({ row }) => {
            const status = row.original.deleted_at === null ? 'Aktywny' : 'Nieaktywny';
            const colorClass = row.original.deleted_at === null ? 'text-green-600' : 'text-red-600';
            return h('span', { class: colorClass + ' font-medium' }, status);
        },
    },
    {
        id: 'actions',
        header: () => h('div', { class: 'w-full text-right' }, 'Akcje'),
        cell: ({ row }) => {
            const product = row.original;
            return h('div', { class: 'flex justify-end space-x-2' }, [
                product.deleted_at === null
                    ? h(Link, { href: route('products.edit', product.id) }, () => h(Button, { variant: 'outline', size: 'sm' }, { default: () => 'Edytuj' }))
                    : null,
                product.deleted_at !== null
                    ? h(Button, { variant: 'outline', size: 'sm', onClick: () => restoreProduct(product.id) }, { default: () => 'Przywróć' })
                    : null,
                product.deleted_at === null
                    ? h(Button, { variant: 'destructive', size: 'sm', onClick: () => confirmDeactivate(product) }, { default: () => 'Wyłącz' })
                    : null,
            ]);
        },
    },
];
</script>

<template>
    <Head title="Zarządzanie Produktami" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Lista Produktów</h3>
                    <div class="flex items-center space-x-2">
                        <Switch id="show-disabled-switch" :model-value="showDisabled" @update:model-value="showDisabled = $event" />
                        <Label for="show-disabled-switch">Pokaż nieaktywne</Label>
                    </div>
                </div>
                <DataTable
                    :columns="columns"
                    :data="props.products.data"
                    :current-page="props.products.current_page"
                    :last-page="props.products.last_page"
                    :total="props.products.total"
                    :per-page="props.products.per_page"
                    @update:page="handlePageUpdate"
                    @update:filter="handleFilterUpdate"
                    @update:sort="handleSortUpdate"
                    :sort-by="props.sort_by"
                    :sort-direction="props.sort_direction"
                />

                <div class="mt-6">
                    <Link :href="route('products.create')">
                        <Button class="w-full">Dodaj Produkt</Button>
                    </Link>
                </div>
            </div>
        </div>
        <AlertDialog :open="isAlertDialogOpen" @update:open="isAlertDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Czy na pewno chcesz dezaktywować ten produkt?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Ta akcja spowoduje ukrycie produktu "{{ productToDeactivate?.name }}". Będzie go można przywrócić w przyszłości.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="isAlertDialogOpen = false">Anuluj</AlertDialogCancel>
                    <AlertDialogAction @click="deactivateProductConfirmed" class="bg-red-600 text-white hover:bg-red-700">
                        Dezaktywuj
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
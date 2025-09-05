<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { ProductTypeIndexProps } from '@/types';
import type { ProductType } from '@/types/models';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch, h, defineAsyncComponent } from 'vue';
import { toast } from 'vue-sonner';
import { ColumnDef } from '@tanstack/vue-table';
import { Button } from '@/components/ui/button';
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

const props = defineProps<ProductTypeIndexProps>();

const currentPage = ref(props.productTypes.current_page);
const currentGlobalFilter = ref(props.filter);
const showDisabled = ref(props.show_disabled);

const form = useForm({});
const isAlertDialogOpen = ref(false);
const itemToDelete = ref<Pick<ProductType, 'id' | 'name'> | null>(null);

const confirmDelete = (productType: Pick<ProductType, 'id' | 'name'>) => {
    itemToDelete.value = productType;
    isAlertDialogOpen.value = true;
};

const deleteItemConfirmed = () => {
    if (itemToDelete.value) {
        form.delete(route('product-types.destroy', itemToDelete.value.id), {
            onSuccess: () => {
                toast.success('Typ produktu został pomyślnie zarchiwizowany.');
                isAlertDialogOpen.value = false;
                itemToDelete.value = null;
                fetchTableData();
            },
            onError: () => toast.error(props.flash?.error || 'Wystąpił błąd podczas archiwizacji.'),
        });
    }
};

const restoreItem = (id: number) => {
    router.post(route('product-types.restore', id), {}, {
        onSuccess: () => {
            toast.success('Typ produktu został pomyślnie przywrócony.');
            fetchTableData();
        },
        onError: () => toast.error('Wystąpił błąd podczas przywracania.'),
    });
};

watch(showDisabled, (newValue) => {
    router.get(route('product-types.index', { show_disabled: newValue }), {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['productTypes', 'show_disabled'],
    });
});

const fetchTableData = () => {
    router.get(
        route('product-types.index'),
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
            only: ['productTypes', 'filter', 'show_disabled', 'sort_by', 'sort_direction'],
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
        route('product-types.index'),
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
            only: ['productTypes', 'filter', 'show_disabled', 'sort_by', 'sort_direction'],
        }
    );
};

const columns: ColumnDef<ProductType>[] = [
    { accessorKey: 'name', header: 'Nazwa', enableSorting: true },
    {
        accessorKey: 'deleted_at',
        header: 'Status',
        cell: ({ row }) => {
            const status = row.original.deleted_at === null ? 'Aktywny' : 'Zarchiwizowany';
            const colorClass = row.original.deleted_at === null ? 'text-green-600' : 'text-red-600';
            return h('span', { class: colorClass + ' font-medium' }, status);
        },
    },
    {
        id: 'actions',
        header: () => h('div', { class: 'w-full text-right' }, 'Akcje'),
        cell: ({ row }) => {
            const item = row.original;
            return h('div', { class: 'flex justify-end space-x-2' }, [
                item.deleted_at === null
                    ? h(Link, { href: route('product-types.edit', item.id ) }, () => h(Button, { variant: 'outline', size: 'sm' }, { default: () => 'Edytuj' }))
                    : null,
                item.deleted_at !== null
                    ? h(Button, { variant: 'outline', size: 'sm', onClick: () => restoreItem(item.id) }, { default: () => 'Przywróć' })
                    : null,
                item.deleted_at === null
                    ? h(Button, { variant: 'destructive', size: 'sm', onClick: () => confirmDelete(item) }, { default: () => 'Archiwizuj' })
                    : null,
            ]);
        },
    },
];
</script>

<template>
    <Head title="Typy Produktów" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Lista Typów Produktów</h3>
                    <div class="flex items-center space-x-2">
                        <Switch id="show-disabled-switch" :model-value="showDisabled" @update:model-value="showDisabled = $event" />
                        <Label for="show-disabled-switch">Pokaż zarchiwizowane</Label>
                    </div>
                </div>
                <DataTable
                    :columns="columns"
                    :data="props.productTypes.data"
                    :current-page="props.productTypes.current_page"
                    :last-page="props.productTypes.last_page"
                    :total="props.productTypes.total"
                    :per-page="props.productTypes.per_page"
                    @update:page="handlePageUpdate"
                    @update:filter="handleFilterUpdate"
                    @update:sort="handleSortUpdate"
                    :sort-by="props.sort_by"
                    :sort-direction="props.sort_direction"
                />
                <div class="mt-6">
                    <Link :href="route('product-types.create')">
                        <Button class="w-full">Dodaj Typ Produktu</Button>
                    </Link>
                </div>
            </div>
        </div>
        <AlertDialog :open="isAlertDialogOpen" @update:open="isAlertDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Czy na pewno chcesz zarchiwizować ten wpis?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Ta akcja spowoduje ukrycie typu produktu "{{ itemToDelete?.name }}". Będzie go można przywrócić w przyszłości.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="isAlertDialogOpen = false">Anuluj</AlertDialogCancel>
                    <AlertDialogAction @click="deleteItemConfirmed" class="bg-red-600 text-white hover:bg-red-700">
                        Archiwizuj
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
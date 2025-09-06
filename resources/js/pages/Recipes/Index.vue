<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type RecipeIndexProps } from '@/types/recipe';
import { ref, watch, h, defineAsyncComponent } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ColumnDef } from '@tanstack/vue-table';
import { toast } from 'vue-sonner';
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

const DataTable = defineAsyncComponent(() =>
  import('@/components/DataTable.vue')
);

const props = defineProps<RecipeIndexProps>();

const currentPage = ref(props.recipes.current_page);
const currentGlobalFilter = ref(props.filter);
const showDisabledRecipes = ref(props.show_disabled);

const form = useForm({});
const isAlertDialogOpen = ref(false);
const recipeToDeleteId = ref<number | null>(null);
const recipeToDeleteName = ref<string>('');

const confirmDelete = (recipeId: number, recipeName: string) => {
    recipeToDeleteId.value = recipeId;
    recipeToDeleteName.value = recipeName;
    isAlertDialogOpen.value = true;
};

const deleteRecipeConfirmed = () => {
    if (recipeToDeleteId.value !== null) {
        form.delete(route('recipes.destroy', recipeToDeleteId.value), {
            onSuccess: () => {
                toast.success(props.flash?.success || 'Receptura została pomyślnie usunięta.');
                isAlertDialogOpen.value = false;
            },
            onError: () => {
                toast.error(props.flash?.error || 'Wystąpił nieoczekiwany błąd podczas usuwania receptury.');
                isAlertDialogOpen.value = false;
            },
            onFinish: () => {
                recipeToDeleteId.value = null;
                recipeToDeleteName.value = '';
            }
        });
    }
};

watch(showDisabledRecipes, (newValue) => {
    fetchTableData({ show_disabled: newValue, page: 1 });
});

const restoreRecipe = (recipeId: number) => {
    router.post(route('recipes.restore', recipeId), {}, {
        onSuccess: () => {
            toast.success('Receptura została pomyślnie przywrócona.');
            fetchTableData();
        },
        onError: () => {
            toast.error('Wystąpił błąd podczas przywracania receptury.');
        },
    });
};

const fetchTableData = (options: { page?: number, filter?: string | null, sort?: string, direction?: string, show_disabled?: boolean } = {}) => {
    router.get(
        route('recipes.index'),
        {
            page: options.page || currentPage.value,
            filter: options.filter === undefined ? currentGlobalFilter.value : options.filter,
            sort: options.sort || props.sort_by,
            direction: options.direction || props.sort_direction,
            show_disabled: options.show_disabled === undefined ? showDisabledRecipes.value : options.show_disabled,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['recipes', 'show_disabled', 'sort_by', 'sort_direction', 'filter'],
        }
    );
};

const handlePageUpdate = (newPage: number) => {
    currentPage.value = newPage;
    fetchTableData({ page: newPage });
};

const handleFilterUpdate = (newFilter: string) => {
    currentGlobalFilter.value = newFilter;
    currentPage.value = 1;
    fetchTableData({ filter: newFilter, page: 1 });
};

const handleSortUpdate = (sortData: { column: string, direction: string }) => {
    fetchTableData({ sort: sortData.column, direction: sortData.direction, page: 1 });
};

const columns: ColumnDef<typeof props.recipes.data[0]>[] = [
    {
        accessorKey: 'id',
        header: 'ID',
        enableSorting: true,
    },
    {
        accessorKey: 'name',
        header: 'Nazwa receptury',
        enableSorting: true,
    },
    {
        accessorKey: 'product_name',
        header: 'Produkt wynikowy',
        enableSorting: true,
    },
    {
        accessorKey: 'created_at',
        header: 'Data utworzenia',
        enableSorting: true,
    },
    {
        accessorKey: 'deleted_at',
        header: 'Status',
        cell: ({ row }) => {
            const status = row.original.deleted_at === null ? 'Aktywna' : 'Wyłączona';
            const colorClass = row.original.deleted_at === null ? 'text-green-600' : 'text-red-600';
            return h('span', { class: colorClass + ' font-medium' }, status);
        },
        enableSorting: false,
    },
    {
        id: 'actions',
        header: () => h('div', { class: 'w-full text-right' }, 'Akcje'),
        cell: ({ row }) => {
            const recipe = row.original;
            return h('div', { class: 'flex justify-end space-x-2 text-right' }, [
                recipe.deleted_at === null
                    ? h(Link, { href: route('recipes.edit', recipe.id) }, () => h(Button, { variant: 'outline', size: 'sm' }, { default: () => 'Edytuj' }))
                    : null,
                recipe.deleted_at !== null
                    ? h(Button, { variant: 'outline', size: 'sm', onClick: () => restoreRecipe(recipe.id) }, { default: () => 'Przywróć' })
                    : null,
                recipe.deleted_at === null
                    ? h(Button, {
                          variant: 'destructive',
                          size: 'sm',
                          onClick: () => confirmDelete(recipe.id, recipe.name),
                      }, { default: () => 'Wyłącz' })
                    : null,
            ]);
        },
    },
];
</script>

<template>
    <Head title="Receptury" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Lista Receptur</h3>
                    <div class="flex items-center space-x-2">
                            <Switch
                                id="show-disabled-recipes"
                                v-model:checked="showDisabledRecipes"
                            />
                            <Label for="show-disabled-recipes">Pokaż wyłączone</Label>
                    </div>
                </div>
                <DataTable
                    :columns="columns"
                    :data="props.recipes.data"
                    :current-page="props.recipes.current_page"
                    :last-page="props.recipes.last_page"
                    :total="props.recipes.total"
                    :per-page="props.recipes.per_page"
                    @update:page="handlePageUpdate"
                    @update:filter="handleFilterUpdate"
                    @update:sort="handleSortUpdate"
                    :sort-by="props.sort_by"
                    :sort-direction="props.sort_direction"
                    :filter="props.filter"
                />

                <div class="mt-6">
                    <Link :href="route('recipes.create')">
                        <Button class="w-full">Dodaj nową recepturę</Button>
                    </Link>
                </div>
            </div>
        </div>
        <AlertDialog :open="isAlertDialogOpen" @update:open="isAlertDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Czy na pewno chcesz wyłączyć recepturę?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Ta akcja spowoduje wyłączenie receptury **{{ recipeToDeleteName }}**. Będzie można ją przywrócić w przyszłości.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="isAlertDialogOpen = false">Anuluj</AlertDialogCancel>
                    <AlertDialogAction @click="deleteRecipeConfirmed" class="bg-red-600 text-white hover:bg-red-700">
                        Wyłącz
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
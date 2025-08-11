<script setup lang="ts" generic="TData, TValue">
import { ref, watch, computed } from 'vue';
import {
    FlexRender,
    getCoreRowModel,
    useVueTable,
    ColumnDef,
    getFilteredRowModel,
} from '@tanstack/vue-table';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { ArrowUpDown, ChevronDown } from 'lucide-vue-next';

interface DataTableProps<TData, TValue> {
    columns: ColumnDef<TData, TValue>[];
    data: TData[];
    currentPage: number;
    lastPage: number;
    total: number;
    perPage: number;
    sortBy: string | null;
    sortDirection: 'asc' | 'desc' | null;
}

const props = defineProps<DataTableProps<TData, TValue>>();
const globalFilter = ref('');

const table = useVueTable({
    get data() { return props.data; },
    get columns() { return props.columns; },
    getCoreRowModel: getCoreRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    state: {
        get globalFilter() { return globalFilter.value; },
        get pagination() {
            return {
                pageIndex: props.currentPage - 1,
                pageSize: props.perPage,
            };
        },
        get sorting() {
            if (props.sortBy && props.sortDirection) {
                return [{ id: props.sortBy, desc: props.sortDirection === 'desc' }];
            }
            return [];
        },
    },
    manualSorting: true,
    manualPagination: true,
    get pageCount() { return props.lastPage; },
    get rowCount() { return props.total; },
});

const emit = defineEmits(['update:page', 'update:filter', 'update:sort']);

watch(globalFilter, (newFilter) => {
    emit('update:page', 1);
    emit('update:filter', newFilter);
});

const displayedPages = computed(() => {
    const { currentPage, lastPage } = props;
    const pages = [];

    if (lastPage <= 10) {
        for (let i = 1; i <= lastPage; i++) {
            pages.push(i);
        }
        return pages;
    }

    pages.push(1);

    if (currentPage > 4) {
        pages.push('...');
    }

    const start = Math.max(2, currentPage - 2);
    const end = Math.min(lastPage - 1, currentPage + 3);
    for (let i = start; i <= end; i++) {
        if (i > 1 && i < lastPage) {
            pages.push(i);
        }
    }

    if (currentPage < lastPage - 4) {
        pages.push('...');
    }

    if (!pages.includes(lastPage) && lastPage > 1) {
        pages.push(lastPage);
    }
    
    const uniquePages = [...new Set(pages)];
    return uniquePages;
});
</script>

<template>
    <div class="w-full">
        <div class="flex items-center py-4">
            <Input
                placeholder="Wyszukaj..."
                v-model="globalFilter"
                class="max-w-sm"
            />
        </div>
        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id" :colSpan="header.colSpan">
                            <div v-if="!header.isPlaceholder">
                                <Button
                                    v-if="header.column.getCanSort()"
                                    variant="ghost"
                                    @click="
                                        emit(
                                            'update:sort',
                                            {
                                                column: header.column.id,
                                                direction: header.column.getIsSorted() === 'asc' ? 'desc' : 'asc'
                                            }
                                        )
                                    "
                                    class="p-0 hover:bg-transparent"
                                >
                                    <FlexRender :render="header.column.columnDef.header" :props="header.getContext()" />
                                    <template v-if="header.column.getIsSorted() === 'asc'">
                                        <ChevronDown class="ml-2 h-4 w-4 rotate-180" />
                                    </template>
                                    <template v-else-if="header.column.getIsSorted() === 'desc'">
                                        <ChevronDown class="ml-2 h-4 w-4" />
                                    </template>
                                    <template v-else>
                                        <ArrowUpDown class="ml-2 h-4 w-4" />
                                    </template>
                                </Button>
                                <FlexRender v-else :render="header.column.columnDef.header" :props="header.getContext()" />
                            </div>
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="table.getRowModel().rows?.length">
                        <TableRow
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                            :data-state="row.getIsSelected() && 'selected'"
                        >
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell :colspan="columns.length" class="h-24 text-center">
                                Brak wyników.
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>
        <div class="flex items-center justify-end space-x-2 py-4">
            <Button
                variant="outline"
                size="sm"
                :disabled="!table.getCanPreviousPage()"
                @click="emit('update:page', props.currentPage - 1)"
            >
                Poprzednia
            </Button>

            <template v-for="(page, index) in displayedPages" :key="index">
                <span v-if="page === '...'" class="px-2">...</span>
                <Button
                    v-else
                    variant="outline"
                    size="sm"
                    :class="{'bg-gray-200 dark:bg-gray-700': page === props.currentPage, 'font-bold': page === props.currentPage}"
                    @click="emit('update:page', page)"
                >
                    {{ page }}
                </Button>
            </template>

            <Button
                variant="outline"
                size="sm"
                :disabled="!table.getCanNextPage()"
                @click="emit('update:page', props.currentPage + 1)"
            >
                Następna
            </Button>
        </div>
    </div>
</template>
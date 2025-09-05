/* eslint-disable @typescript-eslint/no-empty-object-type */
import type { PageProps, PaginatedData } from '@/types';
import type { Category } from '@/types/models';

interface CategoriesData extends PaginatedData {
    data: Category[];
}

export interface CategoryIndexProps extends PageProps {
    categories: CategoriesData;
    filter: string | null;
    show_disabled: boolean;
    sort_by: string | null;
    sort_direction: 'asc' | 'desc' | null;
}

export interface CategoryCreateProps extends PageProps {}

export interface CategoryEditProps extends PageProps {
    category: Category;
}
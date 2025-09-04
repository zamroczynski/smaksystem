import type { PageProps, PaginatedData } from '@/types';
import type { ProductType } from '@/types/models';

interface ProductTypesData extends PaginatedData {
    data: ProductType[];
}

export interface ProductTypeIndexProps extends PageProps {
    productTypes: ProductTypesData;
    filter: string | null;
    show_disabled: boolean;
    sort_by: string | null;
    sort_direction: 'asc' | 'desc' | null;
}

export interface ProductTypeCreateProps extends PageProps {}

export interface ProductTypeEditProps extends PageProps {
    productType: ProductType;
}
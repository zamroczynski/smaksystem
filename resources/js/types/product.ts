import type { PageProps, PaginatedData } from '@/types';
import type { Product, ProductType, Category, UnitOfMeasure, VatRate } from '@/types/models';

interface ProductsData extends PaginatedData {
    data: Product[];
}

export interface ProductIndexProps extends PageProps {
    products: ProductsData;
    filter: string | null;
    show_disabled: boolean;
    sort_by: string | null;
    sort_direction: 'asc' | 'desc' | null;
}

interface ProductFormParams {
    productTypes: ProductType[];
    categories: Category[];
    unitsOfMeasure: UnitOfMeasure[];
    vatRates: VatRate[];
}

export interface ProductCreateProps extends PageProps, ProductFormParams {}

export interface ProductEditProps extends PageProps, ProductFormParams {
    product: Product;
}


import type { PageProps, PaginatedData } from '@/types';
import type { Product, ProductType, Category, UnitOfMeasure, VatRate } from '@/types/models';
import type { FormDataConvertible } from '@inertiajs/core';

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

export interface ProductFormData {
    name: string;
    sku: string;
    description: string;
    product_type_id: number | null;
    category_id: number | null;
    unit_of_measure_id: number | null;
    vat_rate_id: number | null;
    is_sellable: boolean;
    is_inventoried: boolean;
    selling_price: string | number;
    default_purchase_price: string | number;
}

export type ProductFormType = ProductFormData & Record<string, FormDataConvertible | null>;

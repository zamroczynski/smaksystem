import type { PageProps, PaginatedData } from '@/types';
import type { VatRate } from '@/types/models';

interface VatRatesData extends PaginatedData {
    data: VatRate[];
}

export interface VatRateIndexProps extends PageProps {
    vatRates: VatRatesData;
    filter: string | null;
    show_disabled: boolean;
    sort_by: string | null;
    sort_direction: 'asc' | 'desc' | null;
}

export interface VatRateCreateProps extends PageProps {}

export interface VatRateEditProps extends PageProps {
    vatRate: VatRate;
}
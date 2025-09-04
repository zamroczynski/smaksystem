import type { PageProps, PaginatedData } from '@/types';
import type { UnitOfMeasure } from '@/types/models';

interface UnitOfMeasuresData extends PaginatedData {
    data: UnitOfMeasure[];
}

export interface UnitOfMeasureIndexProps extends PageProps {
    unitOfMeasures: UnitOfMeasuresData;
    filter: string | null;
    show_disabled: boolean;
    sort_by: string | null;
    sort_direction: 'asc' | 'desc' | null;
}

export interface UnitOfMeasureCreateProps extends PageProps {}

export interface UnitOfMeasureEditProps extends PageProps {
    unitOfMeasure: UnitOfMeasure;
}
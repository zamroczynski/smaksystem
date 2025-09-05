/* eslint-disable @typescript-eslint/no-empty-object-type */
import { type ShiftTemplate } from '@/types/models';
import { type PaginatedData, type PageProps } from '@/types';

interface ShiftTemplatesData extends PaginatedData {
    data: ShiftTemplate[];
}

export interface ShiftTemplateIndexProps extends PageProps {
    shiftTemplates: ShiftTemplatesData;
    show_deleted: boolean;
    filter?: string | null;
    sort_by?: string | null;
    sort_direction?: 'asc' | 'desc' | null;
}

export interface ShiftTemplateCreateProps extends PageProps {}

export interface ShiftTemplateEditProps extends PageProps {
    shiftTemplate: ShiftTemplate;
}
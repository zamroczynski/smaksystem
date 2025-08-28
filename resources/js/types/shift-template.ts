import { type ShiftTemplate } from '@/types/models';
import { type PaginatedData, type PageProps } from '@/types';

interface ShiftTemplatesData extends PaginatedData {
    data: ShiftTemplate[];
}

export interface ShiftTemplateIndexProps extends PageProps {
    shiftTemplates: ShiftTemplatesData;
    show_deleted: boolean;
}

export interface ShiftTemplateCreateProps extends PageProps {
    tempData: null;
}

export interface ShiftTemplateEditProps extends PageProps {
    shiftTemplate: ShiftTemplate;
}
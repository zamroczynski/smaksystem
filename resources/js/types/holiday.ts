import type {  PaginatedData, PageProps } from '@/types';
import type {  Holiday} from '@/types/models';

interface HolidaysData extends PaginatedData {
    data: Holiday[]
}

interface BaseHoliday {
    id: number;
    name: string;
}

export interface HolidayIndexProps extends PageProps {
    holidays: HolidaysData;
    filter: string | null;
    show_archived: boolean;
    sort_by: string | null;
    sort_direction: 'asc' | 'desc' | null;
}

export interface HolidayCreateProps extends PageProps {
    baseHolidays: BaseHoliday[];
}

export type HolidayEditForm = {
    name: string;
    date: string;
    day_month: string;
    calculation_rule: {
        base_type: string;
        base_event: string | null;
        base_holiday_id: number | null;
        offset: number;
    } | null;
}

export interface HolidayEditProps extends PageProps {
    holiday: Holiday;
    baseHolidays: BaseHoliday[];
}
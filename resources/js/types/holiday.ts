import { type PaginatedData, type PageProps } from '@/types';
import { type Holiday, CalculationRule} from '@/types/models';

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

export interface HolidayEditForm {
    name: string;
    date: string;
    day_month: string;
    calculation_rule: CalculationRule | null;
}

export interface HolidayEditProps extends PageProps {
    holiday: Holiday;
    baseHolidays: BaseHoliday[];
}
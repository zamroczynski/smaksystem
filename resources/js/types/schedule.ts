import { type UserPreferences, type PaginatedData, type PageProps } from '@/types';
import { type User, type Schedule, type ShiftTemplate} from '@/types/models';

export interface SchedulesData extends PaginatedData {
    data: Schedule[];
}

export interface ScheduleIndexProps extends PageProps {
    schedules: SchedulesData;
    show_archived: boolean;
    filter?: string | null;
    sort_by?: string | null;
    sort_direction?: 'asc' | 'desc' | null;
}

export interface ScheduleEditProps extends PageProps {
    schedule: Schedule;
    assignedShiftTemplates: {
        id: number;
        name: string;
        time_from: string;
        time_to: string;
        duration_hours: number;
        required_staff_count: number;
    }[];
    users: {
        id: number;
        name: string;
    }[];
    initialAssignments: Record<string, number | null>;
    monthDays: {
        date: string;
        day_number: number;
        is_sunday: boolean;
        is_saturday: boolean;
        is_holiday: boolean;
    }[];
    preferences: Record<string, UserPreferences>;
}

export interface ScheduleCreateProps extends PageProps {
    activeShiftTemplates: ShiftTemplate[];
    errors: Record<string, string>;
}

interface DayData {
    date: string;
    day_number: number;
    day_name_short: string;
    is_sunday: boolean;
    is_saturday: boolean;
    is_holiday: boolean;
}

interface AssignmentData {
    user_id: number;
    user_name: string;
}

interface ScheduleDetails {
    schedule: {
        id: number;
        name: string;
        period_start_date: string;
        status: 'published' | 'archived';
    };
    shiftTemplates: ShiftTemplate[];
    users: User[];
    assignments: Record<string, AssignmentData[]>;
    monthDays: DayData[];
}

export interface ScheduleShowProps extends PageProps {
    scheduleData: ScheduleDetails;
}

export interface ScheduleViewProps extends PageProps {
    schedules: SchedulesData;
}
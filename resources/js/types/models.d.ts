interface TimestampsModel {
    created_at: string;
    updated_at: string
    deleted_at?: string | null;
}

export interface User extends TimestampsModel {
    id: number;
    login: string;
    name: string;
    email: string;
    email_verified_at: string | null;
    role?: string;
}

export interface Role extends TimestampsModel {
    id: number;
    name: string;
    guard_name?: string;
    is_assigned_to_users?: boolean;
}

export interface Permission extends TimestampsModel {
    id: number;
    name: string;
    guard_name?: string;
}

export interface Preference extends TimestampsModel {
    id: number;
    description?: string | number;
    date_from: string;
    date_to: string;
    is_active: boolean;
    availability: boolean;
}

export interface Schedule extends TimestampsModel {
    id: number;
    name: string;
    period_start_date: string;
    status: 'draft' | 'published' | 'archived';
}

export interface ShiftTemplate extends TimestampsModel {
    id: number;
    name: string;
    time_from: string; 
    time_to: string;  
    duration_hours: number;
    required_staff_count: number; 
}

export interface CalculationRule {
    base_type: 'event' | 'holiday';
    base_event: 'easter' | null;
    base_holiday_id: number | null;
    offset: number;
}

export interface Holiday extends TimestampsModel {
    id: number;
    name: string;
    date: string | null;
    day_month: string | null;
    calculation_rule: CalculationRule | null;
}
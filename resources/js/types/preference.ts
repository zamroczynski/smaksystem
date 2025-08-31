import type {  BreadcrumbItem, PaginatedData, PageProps } from '@/types';
import type {  Preference } from '@/types/models';

interface PreferenceData extends PaginatedData {
    data: Preference[];
}

export interface PreferenceIndexProps extends PageProps {
    preferences: PreferenceData;
    show_inactive_or_deleted: boolean;
    filter?: string | null;
    sort_by?: string | null;
    sort_direction?: 'asc' | 'desc' | null;
}

export interface PreferenceCreateProps {
    breadcrumbs: BreadcrumbItem[];
}

export interface PreferenceEditProps extends PageProps {
    preference: Preference;
}
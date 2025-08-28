import { type BreadcrumbItem, Links, Flash } from '@/types';
import { type Preference } from '@/types/models';

export interface PreferenceIndexProps {
    preferences: {
        data: Preference[];
        links: Links[];
        current_page: number;
        from: number;
        last_page: number;
        per_page: number;
        to: number;
        total: number;
    };
    flash?: Flash;
    show_inactive_or_deleted: boolean;
    breadcrumbs: BreadcrumbItem[]
}

export interface PreferenceCreateProps {
    breadcrumbs: BreadcrumbItem[]
}

export interface PreferenceEditProps {
    preference: Preference;
    breadcrumbs: BreadcrumbItem[]
}
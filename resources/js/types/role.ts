import { type BreadcrumbItem, Links, Flash } from '@/types';
import { type Permission, Role } from '@/types/models';

export interface RoleIndexProps {
        roles: {
        data: Role[];
        links: Links[];
        current_page: number;
        from: number;
        last_page: number;
        per_page: number;
        to: number;
        total: number;
    };
    flash?: Flash;
    show_disabled: boolean;
    breadcrumbs: BreadcrumbItem[]
}

export interface RoleEditProps {
    role: Role;
    allPermissions: Permission[];
    rolePermissions: string[];
    breadcrumbs: BreadcrumbItem[];
}

export interface RoleCreateProps {
    permissions: Permission[];
    breadcrumbs: BreadcrumbItem[];
}
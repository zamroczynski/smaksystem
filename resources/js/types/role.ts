import type { BreadcrumbItem, PageProps, PaginatedData } from '@/types';
import type { Permission, Role } from '@/types/models';

interface RoleData extends PaginatedData {
    data: Role[];
}

export interface RoleIndexProps extends PageProps {
    roles: RoleData;
    show_disabled: boolean;
    filter?: string | null;
    sort_by?: string | null;
    sort_direction?: 'asc' | 'desc' | null;
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
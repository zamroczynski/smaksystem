import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';
import { PageProps as InertiaPageProps } from '@inertiajs/core';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    roles: string;
    role_permissions: string[];
}

export type BreadcrumbItemType = BreadcrumbItem;

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps {
        auth: {
            user: User | null;
            role_permissions: string[];
        };
        ziggy: Config & { location: string };
        flash: {
            success?: string;
            error?: string;
        };
    }
}

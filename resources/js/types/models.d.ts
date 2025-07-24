export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string | null;
    updated_at: string | null;
    deleted_at: string | null;
    roles?: string[];
}

export interface Role {
    id: number;
    name: string;
    guard_name?: string;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string | null;
    is_assigned_to_users?: boolean;
}

export interface Permission {
    id: number;
    name: string;
    guard_name?: string;
    created_at?: string;
    updated_at?: string;
}

export interface Preference {
    id: number;
    description: string | null;
    date_from: string; // Format YYYY-MM-DD
    date_to: string; // Format YYYY-MM-DD
    is_active: boolean;
    deleted_at: string | null;
}
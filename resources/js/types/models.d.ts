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
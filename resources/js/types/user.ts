import { type PaginatedData, type PageProps } from '@/types';
import { type User, type Role } from '@/types/models';

export interface UserPreferences {
    [date: string]: boolean;
}

interface UserData extends PaginatedData {
    data: User[];
}

export interface UserIndexProps extends PageProps {
    users: UserData;
    show_disabled: boolean;
    filter: string | null;
    sort_by: string | null;
    sort_direction: 'asc' | 'desc' | null;
    auth: {
        user: User,
    }
}

export interface UserCreateProps extends PageProps {
    roles: Role[]; 
}

export interface UserEditProps extends PageProps {
    user: User;
    roles: Role[];
}
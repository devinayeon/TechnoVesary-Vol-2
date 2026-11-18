export type UserRole = 'admin' | 'driver' | 'customer';

export interface CustomerProfile {
    id: number;
    user_id: number;
    points: number;
    is_member: boolean;
    membership_expires_at: string | null;
}

export interface DriverProfile {
    id: number;
    user_id: number;
    is_available: boolean;
}

export interface AdminProfile {
    id: number;
    user_id: number;
}

export type User = {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    photo?: string | null;
    avatar?: string;
    phone_number?: string | null;
    address?: string | null;
    latitude?: number | string | null;
    longitude?: number | string | null;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    customer?: CustomerProfile | null;
    driver?: DriverProfile | null;
    admin?: AdminProfile | null;
    [key: string]: unknown;
};

export type Auth = {
    user: User | null;
};

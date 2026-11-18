export type PickupStatusValue =
    | 'pending'
    | 'on_the_way'
    | 'picked_up'
    | 'completed'
    | 'canceled';

export interface PickupStatusOption {
    value: PickupStatusValue;
    label: string;
    short_label?: string;
    tone?: string;
}

export interface PickupUser {
    id: number;
    name: string;
    email?: string;
    photo: string | null;
    phone_number?: string | null;
    role: string;
}

export interface PickupDetailItem {
    id: number;
    waste_deposit_id: number;
    waste_category_id: number;
    points_per_kg: number;
    weight: number | string | null;
    subtotal_points: number | null;
    waste_category?: {
        id: number;
        name: string;
        points_per_kg?: number;
    } | null;
}

export interface PickupSummary {
    id: number;
    customer_id?: number;
    driver_id?: number | null;
    status: PickupStatusValue;
    address: string;
    notes: string | null;
    latitude: string | number | null;
    longitude: string | number | null;
    photo: string | null;
    photo_url?: string | null;
    pickup_proof_photo: string | null;
    pickup_proof_photo_url?: string | null;
    pickup_at: string | null;
    created_at: string;
    updated_at: string;
    total_points?: number;
    total_weight?: number;
    customer?: {
        id: number;
        user?: PickupUser | null;
    } | null;
    driver?: {
        id: number;
        current_latitude?: string | number | null;
        current_longitude?: string | number | null;
        location_updated_at?: string | null;
        user?: PickupUser | null;
    } | null;
    details?: PickupDetailItem[];
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedPickups {
    data: PickupSummary[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: PaginationLink[];
    prev_page_url: string | null;
    next_page_url: string | null;
}

export interface WasteCategoryOption {
    id: number;
    name: string;
    description: string | null;
    points_per_kg: number;
}

export interface MembershipInfo {
    is_member: boolean;
    expires_at: string | null;
}

export interface ActivePickupInfo {
    id: number;
    status: PickupStatusValue;
    status_label?: string;
    address: string;
    customer_name?: string | null;
}

import type { PaginatedData } from './pagination';

export type PointTypeValue = 'in' | 'out';

export interface PointTypeOption {
    value: PointTypeValue;
    label: string;
    short_label?: string;
    tone?: string;
}

export interface PointHistoryItem {
    id: number;
    customer_id: number;
    type: PointTypeValue;
    amount: number;
    source: string;
    created_at: string;
    updated_at: string;
}

export type PaginatedPointHistories = PaginatedData<PointHistoryItem>;

export interface PointSummary {
    total_in: number;
    total_out: number;
    balance: number;
}

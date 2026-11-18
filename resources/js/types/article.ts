export interface ArticleCategory {
    id: number;
    name: string;
    articles_count?: number;
}

export interface ArticleAuthorUser {
    id: number;
    name: string;
    email?: string;
    photo: string | null;
    role: string;
}

export interface ArticleAuthor {
    id: number;
    user?: ArticleAuthorUser | null;
}

export interface ArticleSummary {
    id: number;
    title: string;
    slug: string;
    photo: string | null;
    photo_url?: string | null;
    content: string;
    created_at: string;
    updated_at?: string;
    deleted_at?: string | null;
    category?: ArticleCategory | null;
    admin?: ArticleAuthor | null;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedArticles {
    data: ArticleSummary[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: PaginationLink[];
    prev_page_url: string | null;
    next_page_url: string | null;
}

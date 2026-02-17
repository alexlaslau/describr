export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export interface ProductLink {
    id: number;
    product_id: number;
    url: string;
    status: 'pending' | 'scraping' | 'scraped' | 'failed';
    error_message?: string | null;
    scraped_at?: string | null;
    created_at: string;
    updated_at: string;
}

export interface ScrapeResult {
    id: number;
    product_id: number;
    title: string;
    result: string;
    created_at: string;
    updated_at: string;
}

export interface Product {
    id: number;
    user_id: number;
    name: string;
    status: 'pending' | 'scraping' | 'scraped' | 'generating' | 'completed' | 'failed';
    generated_description?: string | null;
    generated_at?: string | null;
    product_links_count?: number;
    product_links?: ProductLink[];
    scrape_results?: ScrapeResult[];
    created_at: string;
    updated_at: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};

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

export interface GeneratedDescription {
    id: number;
    product_id: number;
    title: string;
    description: string;
    translations?: DescriptionTranslation[];
    created_at: string;
    updated_at: string;
}

export interface DescriptionTranslation {
    id: number;
    generated_description_id: number;
    target_language: string;
    source_language?: string | null;
    provider: string;
    status: 'pending' | 'processing' | 'completed' | 'failed';
    translated_text?: string | null;
    error_message?: string | null;
    translated_at?: string | null;
    created_at: string;
    updated_at: string;
}

export interface ProductImage {
    id: number;
    product_id: number;
    product_link_id: number;
    url: string;
    alt: string | null;
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
    generated_descriptions?: GeneratedDescription[];
    images?: ProductImage[];
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

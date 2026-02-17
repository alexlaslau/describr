import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Product, PageProps } from '@/types';
import { Head, Link } from '@inertiajs/react';

const STATUS_STYLES: Record<Product['status'], { bg: string; text: string; label: string }> = {
    pending: { bg: 'bg-gray-100', text: 'text-gray-600', label: 'Pending' },
    scraping: { bg: 'bg-amber-50', text: 'text-amber-700', label: 'Scraping' },
    scraped: { bg: 'bg-blue-50', text: 'text-blue-700', label: 'Scraped' },
    generating: { bg: 'bg-purple-50', text: 'text-purple-700', label: 'Generating' },
    completed: { bg: 'bg-green-50', text: 'text-green-700', label: 'Completed' },
    failed: { bg: 'bg-red-50', text: 'text-red-700', label: 'Failed' },
};

function StatusBadge({ status }: { status: Product['status'] }) {
    const style = STATUS_STYLES[status] || STATUS_STYLES.pending;
    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${style.bg} ${style.text}`}>
            {style.label}
        </span>
    );
}

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

export default function Index({ products }: PageProps<{ products: Product[] }>) {
    return (
        <AuthenticatedLayout>
            <Head title="Products" />

            <div className="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight text-gray-900">
                            Products
                        </h1>
                        <p className="mt-1 text-sm text-gray-500">
                            {products.length} {products.length === 1 ? 'product' : 'products'}
                        </p>
                    </div>
                    <Link
                        href={route('products.create')}
                        className="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fillRule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clipRule="evenodd" />
                        </svg>
                        New Product
                    </Link>
                </div>

                {/* Content */}
                {products.length === 0 ? (
                    /* Empty state */
                    <div className="mt-16 flex flex-col items-center text-center">
                        <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-50">
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <h3 className="mt-4 text-base font-semibold text-gray-900">No products yet</h3>
                        <p className="mt-2 max-w-xs text-sm text-gray-500">
                            Create your first product and add some links to get AI-generated descriptions.
                        </p>
                        <Link
                            href={route('products.create')}
                            className="mt-6 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700"
                        >
                            Create Your First Product
                        </Link>
                    </div>
                ) : (
                    /* Product list */
                    <div className="mt-8 divide-y divide-gray-100 rounded-xl border border-gray-100">
                        {products.map((product) => (
                            <Link
                                key={product.id}
                                href={route('products.show', product.id)}
                                className="flex items-center justify-between px-6 py-5 transition hover:bg-gray-50/50"
                            >
                                <div className="min-w-0 flex-1">
                                    <div className="flex items-center gap-3">
                                        <h3 className="truncate text-sm font-medium text-gray-900">
                                            {product.name}
                                        </h3>
                                        <StatusBadge status={product.status} />
                                    </div>
                                    <div className="mt-1.5 flex items-center gap-4 text-xs text-gray-400">
                                        <span>{product.product_links_count ?? 0} {(product.product_links_count ?? 0) === 1 ? 'link' : 'links'}</span>
                                        <span>·</span>
                                        <span>{formatDate(product.created_at)}</span>
                                    </div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 flex-shrink-0 text-gray-300" viewBox="0 0 20 20" fill="currentColor">
                                    <path fillRule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clipRule="evenodd" />
                                </svg>
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}

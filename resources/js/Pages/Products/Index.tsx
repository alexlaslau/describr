import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Product, PageProps } from '@/types';
import { Head, Link } from '@inertiajs/react';

const STATUS_STYLES: Record<Product['status'], { bg: string; text: string; label: string; dot: string }> = {
    pending: { bg: 'bg-gray-50', text: 'text-gray-600', label: 'Pending', dot: 'bg-gray-400' },
    scraping: { bg: 'bg-amber-50', text: 'text-amber-700', label: 'Scraping', dot: 'bg-amber-500' },
    scraped: { bg: 'bg-blue-50', text: 'text-blue-700', label: 'Scraped', dot: 'bg-blue-500' },
    generating: { bg: 'bg-purple-50', text: 'text-purple-700', label: 'Generating', dot: 'bg-purple-500' },
    completed: { bg: 'bg-emerald-50', text: 'text-emerald-700', label: 'Completed', dot: 'bg-emerald-500' },
    failed: { bg: 'bg-red-50', text: 'text-red-700', label: 'Failed', dot: 'bg-red-500' },
};

function StatusBadge({ status }: { status: Product['status'] }) {
    const style = STATUS_STYLES[status] || STATUS_STYLES.pending;
    return (
        <span className={`inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium ${style.bg} ${style.text} border-current/10`}>
            <span className={`h-1.5 w-1.5 rounded-full ${style.dot}`} />
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

            <div className="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-gray-900">
                            Products
                        </h1>
                        <p className="mt-1.5 text-sm text-gray-500">
                            {products.length} {products.length === 1 ? 'product' : 'products'}
                        </p>
                    </div>
                    <Link
                        href={route('products.create')}
                        className="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm shadow-indigo-600/20 transition-all hover:bg-indigo-700 hover:shadow-md hover:shadow-indigo-600/25 active:scale-[0.98]"
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
                        <div className="flex h-20 w-20 items-center justify-center rounded-2xl border border-gray-200 bg-white shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-9 w-9 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <h3 className="mt-5 text-base font-semibold text-gray-900">No products yet</h3>
                        <p className="mt-2 max-w-xs text-sm leading-relaxed text-gray-500">
                            Create your first product and add some links to get AI-generated descriptions.
                        </p>
                        <Link
                            href={route('products.create')}
                            className="mt-8 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm shadow-indigo-600/20 transition-all hover:bg-indigo-700 hover:shadow-md hover:shadow-indigo-600/25"
                        >
                            Create Your First Product
                        </Link>
                    </div>
                ) : (
                    /* Product list */
                    <div className="mt-8 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                        <div className="divide-y divide-gray-100">
                            {products.map((product) => (
                                <Link
                                    key={product.id}
                                    href={route('products.show', product.id)}
                                    className="group flex items-center justify-between px-6 py-5 transition-colors hover:bg-gray-50/80"
                                >
                                    <div className="min-w-0 flex-1">
                                        <div className="flex items-center gap-3">
                                            <h3 className="truncate text-sm font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                                {product.name}
                                            </h3>
                                            <StatusBadge status={product.status} />
                                        </div>
                                        <div className="mt-2 flex items-center gap-4 text-xs text-gray-400">
                                            <span className="inline-flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                </svg>
                                                {product.product_links_count ?? 0} {(product.product_links_count ?? 0) === 1 ? 'link' : 'links'}
                                            </span>
                                            <span className="text-gray-300">·</span>
                                            <span>{formatDate(product.created_at)}</span>
                                        </div>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 flex-shrink-0 text-gray-300 transition-all group-hover:translate-x-0.5 group-hover:text-indigo-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fillRule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clipRule="evenodd" />
                                    </svg>
                                </Link>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}

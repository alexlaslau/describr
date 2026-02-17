import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Product, ProductLink, ScrapeResult, PageProps } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';

const STATUS_STYLES: Record<string, { bg: string; text: string; label: string }> = {
    pending: { bg: 'bg-gray-100', text: 'text-gray-600', label: 'Pending' },
    scraping: { bg: 'bg-amber-50', text: 'text-amber-700', label: 'Scraping' },
    scraped: { bg: 'bg-blue-50', text: 'text-blue-700', label: 'Scraped' },
    generating: { bg: 'bg-purple-50', text: 'text-purple-700', label: 'Generating' },
    completed: { bg: 'bg-green-50', text: 'text-green-700', label: 'Completed' },
    failed: { bg: 'bg-red-50', text: 'text-red-700', label: 'Failed' },
};

function StatusBadge({ status }: { status: string }) {
    const style = STATUS_STYLES[status] || STATUS_STYLES.pending;
    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${style.bg} ${style.text}`}>
            {style.label}
        </span>
    );
}

function formatDate(dateString: string | null | undefined) {
    if (!dateString) return '—';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function ScrapeResultCard({ result }: { result: ScrapeResult }) {
    const [expanded, setExpanded] = useState(false);
    const isLong = result.result.length > 300;

    return (
        <div className="rounded-lg border border-gray-100 p-5">
            <h4 className="text-sm font-medium text-gray-900">{result.title}</h4>
            <div className="mt-3 text-sm leading-relaxed text-gray-600">
                {isLong && !expanded ? (
                    <>
                        {result.result.slice(0, 300)}…
                        <button
                            onClick={() => setExpanded(true)}
                            className="ml-1 text-indigo-600 hover:text-indigo-700"
                        >
                            Show more
                        </button>
                    </>
                ) : (
                    <>
                        {result.result}
                        {isLong && (
                            <button
                                onClick={() => setExpanded(false)}
                                className="ml-1 text-indigo-600 hover:text-indigo-700"
                            >
                                Show less
                            </button>
                        )}
                    </>
                )}
            </div>
        </div>
    );
}

export default function Show({ product }: PageProps<{ product: Product }>) {
    const links = product.product_links ?? [];
    const scrapeResults = product.scrape_results ?? [];

    return (
        <AuthenticatedLayout>
            <Head title={product.name} />

            <div className="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
                {/* Back */}
                <Link
                    href={route('products.index')}
                    className="inline-flex items-center gap-1.5 text-sm text-gray-400 transition hover:text-gray-600"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fillRule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clipRule="evenodd" />
                    </svg>
                    Back to Products
                </Link>

                {/* Header */}
                <div className="mt-6 flex items-start justify-between">
                    <div>
                        <div className="flex items-center gap-3">
                            <h1 className="text-2xl font-semibold tracking-tight text-gray-900">
                                {product.name}
                            </h1>
                            <StatusBadge status={product.status} />
                        </div>
                        <p className="mt-1 text-sm text-gray-400">
                            Created {formatDate(product.created_at)}
                        </p>
                    </div>
                    <div className="flex items-center gap-3">
                        <Link
                            href={route('products.create')}
                            className="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 transition hover:border-gray-300 hover:text-gray-900"
                        >
                            Add Links
                        </Link>
                        <button
                            type="button"
                            className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
                        >
                            Re-generate
                        </button>
                    </div>
                </div>

                {/* Generated Description */}
                <section className="mt-10">
                    <h2 className="text-sm font-semibold uppercase tracking-wider text-gray-400">
                        Generated Description
                    </h2>
                    {product.generated_description ? (
                        <div className="mt-4 rounded-xl border border-gray-100 bg-gray-50/50 p-6">
                            <p className="whitespace-pre-wrap text-sm leading-relaxed text-gray-700">
                                {product.generated_description}
                            </p>
                            {product.generated_at && (
                                <p className="mt-4 text-xs text-gray-400">
                                    Generated {formatDate(product.generated_at)}
                                </p>
                            )}
                        </div>
                    ) : (
                        <div className="mt-4 rounded-xl border border-dashed border-gray-200 p-8 text-center">
                            <p className="text-sm text-gray-400">
                                No description generated yet. The AI will create one after scraping completes.
                            </p>
                        </div>
                    )}
                </section>

                {/* Links */}
                <section className="mt-10">
                    <h2 className="text-sm font-semibold uppercase tracking-wider text-gray-400">
                        Links
                        <span className="ml-2 text-gray-300">({links.length})</span>
                    </h2>
                    {links.length === 0 ? (
                        <p className="mt-4 text-sm text-gray-400">No links added yet.</p>
                    ) : (
                        <div className="mt-4 divide-y divide-gray-100 rounded-xl border border-gray-100">
                            {links.map((link: ProductLink) => (
                                <div key={link.id} className="flex items-center justify-between px-5 py-4">
                                    <div className="min-w-0 flex-1">
                                        <a
                                            href={link.url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="truncate text-sm text-indigo-600 hover:text-indigo-700"
                                        >
                                            {link.url}
                                        </a>
                                        {link.scraped_at && (
                                            <p className="mt-0.5 text-xs text-gray-400">
                                                Scraped {formatDate(link.scraped_at)}
                                            </p>
                                        )}
                                    </div>
                                    <StatusBadge status={link.status} />
                                </div>
                            ))}
                        </div>
                    )}
                </section>

                {/* Scrape Results */}
                <section className="mt-10">
                    <h2 className="text-sm font-semibold uppercase tracking-wider text-gray-400">
                        Scrape Results
                        <span className="ml-2 text-gray-300">({scrapeResults.length})</span>
                    </h2>
                    {scrapeResults.length === 0 ? (
                        <p className="mt-4 text-sm text-gray-400">No scrape results available yet.</p>
                    ) : (
                        <div className="mt-4 space-y-4">
                            {scrapeResults.map((result: ScrapeResult) => (
                                <ScrapeResultCard key={result.id} result={result} />
                            ))}
                        </div>
                    )}
                </section>
            </div>
        </AuthenticatedLayout>
    );
}

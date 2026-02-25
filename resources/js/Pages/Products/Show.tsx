import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Product, ProductLink, GeneratedDescription, PageProps } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';

const STATUS_STYLES: Record<string, { bg: string; text: string; label: string; dot: string }> = {
    pending: { bg: 'bg-gray-50', text: 'text-gray-600', label: 'Pending', dot: 'bg-gray-400' },
    scraping: { bg: 'bg-amber-50', text: 'text-amber-700', label: 'Scraping', dot: 'bg-amber-500' },
    scraped: { bg: 'bg-blue-50', text: 'text-blue-700', label: 'Scraped', dot: 'bg-blue-500' },
    generating: { bg: 'bg-purple-50', text: 'text-purple-700', label: 'Generating', dot: 'bg-purple-500' },
    completed: { bg: 'bg-emerald-50', text: 'text-emerald-700', label: 'Completed', dot: 'bg-emerald-500' },
    failed: { bg: 'bg-red-50', text: 'text-red-700', label: 'Failed', dot: 'bg-red-500' },
};

function StatusBadge({ status }: { status: string }) {
    const style = STATUS_STYLES[status] || STATUS_STYLES.pending;
    return (
        <span className={`inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium ${style.bg} ${style.text} border-current/10`}>
            <span className={`h-1.5 w-1.5 rounded-full ${style.dot}`} />
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

function formatDescription(text: string) {
    const sections = text.split(/^###\s+/m).filter(Boolean);

    return sections.map((section, i) => {
        const lines = section.split('\n');
        const heading = lines[0]?.trim();
        const body = lines.slice(1).join('\n').trim();

        return (
            <div key={i} className={i > 0 ? 'mt-6' : ''}>
                {heading && (
                    <h2 className="text-lg font-bold text-gray-900 mb-3">{heading}</h2>
                )}
                {body && (
                    <div className="text-sm leading-relaxed text-gray-600 whitespace-pre-line">
                        {body}
                    </div>
                )}
            </div>
        );
    });
}

function DescriptionCard({ result }: { result: GeneratedDescription }) {
    const [expanded, setExpanded] = useState(false);
    const isLong = result.description.length > 300;

    return (
        <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md">
            <h4 className="text-sm font-semibold text-gray-900">{result.title}</h4>
            <div className="mt-3">
                {isLong && !expanded ? (
                    <>
                        <div className="text-sm leading-relaxed text-gray-600">
                            {result.description.slice(0, 300)}…
                        </div>
                        <button
                            onClick={() => setExpanded(true)}
                            className="mt-2 font-medium text-sm text-indigo-600 hover:text-indigo-700"
                        >
                            Show more
                        </button>
                    </>
                ) : (
                    <>
                        {formatDescription(result.description)}
                        {isLong && (
                            <button
                                onClick={() => setExpanded(false)}
                                className="mt-2 font-medium text-sm text-indigo-600 hover:text-indigo-700"
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

const PROCESSING_STATUSES = ['pending', 'scraping', 'scraped', 'generating'];

const PROCESSING_MESSAGES: Record<string, { title: string; subtitle: string }> = {
    pending: { title: 'Preparing...', subtitle: 'Getting ready to scrape your product links.' },
    scraping: { title: 'Scraping links...', subtitle: 'Fetching product data from your links.' },
    scraped: { title: 'Links scraped!', subtitle: 'Preparing to generate your description...' },
    generating: { title: 'Generating description...', subtitle: 'The AI is writing your product description.' },
};

function ProcessingBanner({ status }: { status: string }) {
    const info = PROCESSING_MESSAGES[status];
    if (!info) return null;

    return (
        <div className="mt-4 rounded-2xl border border-indigo-100 bg-gradient-to-r from-indigo-50 to-purple-50 p-8 text-center">
            <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center">
                <svg className="h-8 w-8 animate-spin text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
            </div>
            <h3 className="text-base font-semibold text-gray-900">{info.title}</h3>
            <p className="mt-1 text-sm text-gray-500">{info.subtitle}</p>
            <div className="mt-4 flex justify-center gap-1.5">
                <span className="h-2 w-2 animate-bounce rounded-full bg-indigo-400" style={{ animationDelay: '0ms' }} />
                <span className="h-2 w-2 animate-bounce rounded-full bg-indigo-400" style={{ animationDelay: '150ms' }} />
                <span className="h-2 w-2 animate-bounce rounded-full bg-indigo-400" style={{ animationDelay: '300ms' }} />
            </div>
        </div>
    );
}

export default function Show({ product }: PageProps<{ product: Product }>) {
    const links = product.product_links ?? [];
    const descriptions = product.generated_descriptions ?? [];
    const isProcessing = PROCESSING_STATUSES.includes(product.status);
    const [copied, setCopied] = useState(false);

    const copyDescription = () => {
        if (!product.generated_description) return;
        navigator.clipboard.writeText(product.generated_description).then(() => {
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        });
    };

    useEffect(() => {
        if (!isProcessing) return;

        const interval = setInterval(() => {
            router.reload({ only: ['product'] });
        }, 3000);

        return () => clearInterval(interval);
    }, [isProcessing]);

    return (
        <AuthenticatedLayout>
            <Head title={product.name} />

            <div className="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
                {/* Back */}
                <Link
                    href={route('products.index')}
                    className="group inline-flex items-center gap-1.5 text-sm text-gray-400 transition hover:text-gray-600"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 transition-transform group-hover:-translate-x-0.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fillRule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clipRule="evenodd" />
                    </svg>
                    Back to Products
                </Link>

                {/* Header */}
                <div className="mt-8 flex items-start justify-between">
                    <div>
                        <div className="flex items-center gap-3">
                            <h1 className="text-2xl font-bold tracking-tight text-gray-900">
                                {product.name}
                            </h1>
                            <StatusBadge status={product.status} />
                        </div>
                        <p className="mt-2 text-sm text-gray-400">
                            Created {formatDate(product.created_at)}
                        </p>
                    </div>
                    <div className="flex items-center gap-3">
                        <Link
                            href={route('products.create')}
                            className="rounded-xl border-2 border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 transition-all hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900"
                        >
                            Add New Product
                        </Link>
                        <button
                            type="button"
                            className="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm shadow-indigo-600/20 transition-all hover:bg-indigo-700 hover:shadow-md hover:shadow-indigo-600/25 active:scale-[0.98]"
                        >
                            Generate
                        </button>
                    </div>
                </div>

                {/* Generated Description */}
                <section className="mt-12">
                    <h2 className="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Generated Description
                    </h2>
                    {product.generated_description ? (
                        <div className="mt-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                            <div className="flex items-center justify-end mb-3">
                                <button
                                    onClick={copyDescription}
                                    className={`inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition-all ${
                                        copied
                                            ? 'bg-emerald-50 text-emerald-600 border border-emerald-200'
                                            : 'bg-gray-50 text-gray-500 border border-gray-200 hover:bg-gray-100 hover:text-gray-700'
                                    }`}
                                >
                                    {copied ? (
                                        <>
                                            <svg xmlns="http://www.w3.org/2000/svg" className="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                            </svg>
                                            Copied!
                                        </>
                                    ) : (
                                        <>
                                            <svg xmlns="http://www.w3.org/2000/svg" className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                            Copy
                                        </>
                                    )}
                                </button>
                            </div>
                            <div className="text-sm leading-relaxed text-gray-700">
                                {formatDescription(product.generated_description)}
                            </div>
                            {product.generated_at && (
                                <p className="mt-5 border-t border-gray-100 pt-4 text-xs text-gray-400">
                                    Generated {formatDate(product.generated_at)}
                                </p>
                            )}
                        </div>
                    ) : isProcessing ? (
                        <ProcessingBanner status={product.status} />
                    ) : (
                        <div className="mt-4 rounded-2xl border-2 border-dashed border-gray-200 p-10 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" className="mx-auto h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p className="mt-3 text-sm text-gray-400">
                                No description generated yet. Click Generate to create one.
                            </p>
                        </div>
                    )}
                </section>

                {/* Links */}
                <section className="mt-12">
                    <h2 className="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Links
                        <span className="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">{links.length}</span>
                    </h2>
                    {links.length === 0 ? (
                        <p className="mt-4 text-sm text-gray-400">No links added yet.</p>
                    ) : (
                        <div className="mt-4 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                            <div className="divide-y divide-gray-100">
                                {links.map((link: ProductLink) => (
                                    <div key={link.id} className="flex items-center justify-between gap-4 px-6 py-4">
                                        <div className="min-w-0 flex-1">
                                            <a
                                                href={link.url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="flex items-center gap-2 text-sm font-medium text-indigo-600 transition-colors hover:text-indigo-800"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" className="h-3.5 w-3.5 flex-shrink-0 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                                <span className="truncate">{link.url}</span>
                                            </a>
                                            {link.scraped_at && (
                                                <p className="mt-1 text-xs text-gray-400">
                                                    Scraped {formatDate(link.scraped_at)}
                                                </p>
                                            )}
                                        </div>
                                        <div className="flex-shrink-0">
                                            <StatusBadge status={link.status} />
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </section>

                {/* Scrape Results */}
                <section className="mt-12">
                    <h2 className="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        Generated Descriptions
                        <span className="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">{descriptions.length}</span>
                    </h2>
                    {descriptions.length === 0 ? (
                        <p className="mt-4 text-sm text-gray-400">No generated descriptions available yet.</p>
                    ) : (
                        <div className="mt-4 space-y-4">
                            {descriptions.map((result: GeneratedDescription) => (
                                <DescriptionCard key={result.id} result={result} />
                            ))}
                        </div>
                    )}
                </section>
            </div>
        </AuthenticatedLayout>
    );
}

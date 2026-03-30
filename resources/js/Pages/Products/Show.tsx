import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { DescriptionTranslation, Product, ProductLink, ProductImage, PageProps } from '@/types';
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

function formatStatusLabel(status: string) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function TranslationCard({ productId, translation, languageLabel, languageFlag }: { productId: number; translation: DescriptionTranslation; languageLabel: string; languageFlag?: string }) {
    const [expanded, setExpanded] = useState(false);
    const statusTone = {
        pending: 'border-amber-200 bg-amber-50 text-amber-700',
        processing: 'border-blue-200 bg-blue-50 text-blue-700',
        completed: 'border-emerald-200 bg-emerald-50 text-emerald-700',
        failed: 'border-red-200 bg-red-50 text-red-700',
    }[translation.status];
    const flag = languageFlag ?? translation.target_language;
    const isLong = (translation.translated_text?.length ?? 0) > 320;

    return (
        <div className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div className="flex items-start justify-between gap-3">
                <div>
                    <h4 className="flex items-center gap-2 text-sm font-semibold text-gray-900">
                        <span className="inline-flex translate-y-px items-center leading-none">{flag}</span>
                        {languageLabel}
                    </h4>
                    <p className="mt-1 text-xs text-gray-400">
                        DeepL{translation.source_language ? ` • detected ${translation.source_language}` : ''}
                    </p>
                </div>
                <div className="flex items-center gap-2">
                    {translation.status === 'completed' && translation.translated_text && (
                        <a
                            href={route('products.translations.pdf', { product: productId, translation: translation.id })}
                            className="inline-flex h-8 items-center gap-1.5 rounded-full border border-gray-200 bg-gray-50 px-3 text-xs font-semibold text-gray-600 transition hover:border-gray-300 hover:bg-gray-100 hover:text-gray-900"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 16V4m0 12l-4-4m4 4l4-4M4 20h16" />
                            </svg>
                            PDF
                        </a>
                    )}
                    <span className={`inline-flex h-8 items-center rounded-full border px-3 text-xs font-semibold ${statusTone}`}>
                        {formatStatusLabel(translation.status)}
                    </span>
                    {isLong && (
                        <button
                            type="button"
                            onClick={() => setExpanded((value) => !value)}
                            className="inline-flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 bg-gray-50 text-gray-500 transition hover:border-gray-300 hover:bg-gray-100 hover:text-gray-700"
                            aria-label={expanded ? 'Collapse translation' : 'Expand translation'}
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                className={`h-3.5 w-3.5 transition-transform ${expanded ? 'rotate-180' : ''}`}
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                strokeWidth={2}
                            >
                                <path strokeLinecap="round" strokeLinejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    )}
                </div>
            </div>

            {translation.translated_text && (
                <div className="mt-4">
                    <div className="text-sm leading-relaxed text-gray-700 whitespace-pre-line">
                        {isLong && !expanded
                            ? `${translation.translated_text.slice(0, 320)}...`
                            : translation.translated_text}
                    </div>
                </div>
            )}

            {!translation.translated_text && translation.status !== 'failed' && (
                <p className="mt-4 text-sm text-gray-500">
                    Translation is running in the background.
                </p>
            )}

            {translation.error_message && (
                <p className="mt-4 rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-sm text-red-600">
                    {translation.error_message}
                </p>
            )}

            {translation.translated_at && (
                <p className="mt-4 border-t border-gray-100 pt-3 text-xs text-gray-400">
                    Translated {formatDate(translation.translated_at)}
                </p>
            )}

            {typeof translation.billed_characters === 'number' && (
                <p className="mt-2 text-xs text-gray-400">
                    DeepL billed {translation.billed_characters} characters.
                </p>
            )}
        </div>
    );
}

const PROCESSING_STATUSES = ['pending', 'scraping', 'scraped', 'generating'];
const TRANSLATION_PROCESSING_STATUSES = ['pending', 'processing'];

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

export default function Show({ product, config }: PageProps<{ product: Product; config: { translationLanguages: Record<string, { label: string; flag: string }>; translationUsage: { character_count: number; character_limit: number } | null } }>) {
    const links = product.product_links ?? [];
    const descriptions = product.generated_descriptions ?? [];
    const images = product.images ?? [];
    const latestDescription = descriptions[0];
    const translations = latestDescription?.translations ?? [];
    const availableTranslationLanguages = Object.entries(config.translationLanguages).filter(([code]) => code !== 'RO');
    const isProcessing = PROCESSING_STATUSES.includes(product.status);
    const [copied, setCopied] = useState(false);
    const [translatingLanguage, setTranslatingLanguage] = useState<string | null>(null);
    const hasPendingTranslations = translations.some((translation) => TRANSLATION_PROCESSING_STATUSES.includes(translation.status));

    const copyDescription = () => {
        if (!product.generated_description) return;
        navigator.clipboard.writeText(product.generated_description).then(() => {
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        });
    };

    const requestTranslation = (language: string) => {
        setTranslatingLanguage(language);
        router.post(route('products.translations.store', { product: product.id }), {
            target_language: language,
        }, {
            preserveScroll: true,
            onFinish: () => setTranslatingLanguage(null),
        });
    };

    useEffect(() => {
        if (!isProcessing && !hasPendingTranslations) return;

        const interval = setInterval(() => {
            router.reload({ only: ['product'] });
        }, 3000);

        return () => clearInterval(interval);
    }, [hasPendingTranslations, isProcessing]);

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
                <div className="mt-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div className="flex flex-wrap items-center gap-2 sm:gap-3">
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
                                <div className="flex items-center gap-2">
                                    <a
                                        href={route('products.descriptions.pdf', { product: product.id })}
                                        className="inline-flex h-8 items-center gap-1.5 rounded-full border border-gray-200 bg-gray-50 px-3 text-xs font-semibold text-gray-600 transition hover:border-gray-300 hover:bg-gray-100 hover:text-gray-900"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M12 16V4m0 12l-4-4m4 4l4-4M4 20h16" />
                                        </svg>
                                        PDF
                                    </a>
                                    <button
                                        onClick={copyDescription}
                                        className={`inline-flex h-8 items-center gap-1.5 rounded-full border px-3 text-xs font-semibold transition-all ${copied
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-600'
                                            : 'border-gray-200 bg-gray-50 text-gray-500 hover:border-gray-300 hover:bg-gray-100 hover:text-gray-700'
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

                {latestDescription && (
                    <section className="mt-8">
                        <div className="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 className="text-xs font-semibold uppercase tracking-wider text-gray-400">
                                        DeepL Translation
                                    </h2>
                                    <p className="mt-1 text-sm text-gray-500">
                                        Translate the latest Romanian description into other supported languages with a dedicated HTTP client integration.
                                    </p>
                                </div>
                                <div className="flex flex-wrap gap-2 sm:justify-end">
                                    {availableTranslationLanguages.map(([code, language]) => {
                                        const translation = translations.find((item) => item.target_language === code);
                                        const hasTranslation = Boolean(translation);
                                        const isBusy = translatingLanguage === code || translation?.status === 'pending' || translation?.status === 'processing';
                                        const isDisabled = isBusy || hasTranslation;
                                        const label = language.label;
                                        const flag = language.flag ?? code;

                                        return (
                                            <button
                                                key={code}
                                                type="button"
                                                onClick={() => requestTranslation(code)}
                                                disabled={isDisabled}
                                                className={`inline-flex min-w-[9.5rem] items-center justify-center rounded-xl border px-4 py-2.5 text-xs font-semibold tracking-wide transition ${isDisabled
                                                    ? 'cursor-not-allowed border-gray-200 bg-gray-100 text-gray-400'
                                                    : 'border-indigo-200 bg-white text-indigo-700 shadow-sm shadow-indigo-100/40 hover:-translate-y-0.5 hover:border-indigo-300 hover:bg-indigo-50 hover:shadow-md'
                                                    }`}
                                            >
                                                <span className="inline-flex items-center gap-2 leading-none">
                                                    <span className="inline-flex translate-y-px items-center leading-none">{flag}</span>
                                                    {isBusy ? `Translating ${code}...` : hasTranslation ? `${label} ready` : label}
                                                </span>
                                            </button>
                                        );
                                    })}
                                </div>
                            </div>

                            {config.translationUsage && (
                                <div className="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-sm text-gray-600">
                                    DeepL usage: {config.translationUsage.character_count.toLocaleString()} / {config.translationUsage.character_limit.toLocaleString()} characters this billing period.
                                </div>
                            )}

                            {translations.length > 0 && (
                                <div className="grid gap-4 lg:grid-cols-2">
                                    {translations.map((translation) => (
                                        <TranslationCard
                                            key={translation.id}
                                            productId={product.id}
                                            translation={translation}
                                            languageLabel={config.translationLanguages[translation.target_language]?.label ?? translation.target_language}
                                            languageFlag={config.translationLanguages[translation.target_language]?.flag}
                                        />
                                    ))}
                                </div>
                            )}
                        </div>
                    </section>
                )}

                {/* Scraped Images */}
                {(images.length > 0 || isProcessing) && (
                    <section className="mt-12">
                        <div className="flex items-center justify-between">
                            <h2 className="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Product Images
                                {images.length > 0 && (
                                    <span className="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">{images.length}</span>
                                )}
                            </h2>
                            {images.length > 0 && (
                                <a
                                    href={route('products.images.download-all', { product: product.id })}
                                    className="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-50 hover:text-gray-900"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download All
                                </a>
                            )}
                        </div>

                        {images.length === 0 && isProcessing ? (
                            <div className="mt-4 rounded-2xl border border-emerald-100 bg-gradient-to-r from-emerald-50 to-teal-50 p-8 text-center">
                                <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center">
                                    <svg className="h-8 w-8 animate-spin text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                    </svg>
                                </div>
                                <h3 className="text-base font-semibold text-gray-900">Scraping images...</h3>
                                <p className="mt-1 text-sm text-gray-500">Extracting product images from your links.</p>
                                <div className="mt-4 flex justify-center gap-1.5">
                                    <span className="h-2 w-2 animate-bounce rounded-full bg-emerald-400" style={{ animationDelay: '0ms' }} />
                                    <span className="h-2 w-2 animate-bounce rounded-full bg-emerald-400" style={{ animationDelay: '150ms' }} />
                                    <span className="h-2 w-2 animate-bounce rounded-full bg-emerald-400" style={{ animationDelay: '300ms' }} />
                                </div>
                            </div>
                        ) : (
                            <div className="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                                {images.map((image: ProductImage) => (
                                    <a
                                        key={image.id}
                                        href={route('products.images.download', { product: product.id, image: image.id })}
                                        className="group relative overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition-all hover:shadow-md hover:border-indigo-200"
                                    >
                                        <img
                                            src={image.url}
                                            alt={image.alt ?? 'Product image'}
                                            className="h-48 w-full object-contain p-2 transition-transform group-hover:scale-105"
                                            onError={(e) => {
                                                (e.currentTarget.parentElement as HTMLElement).style.display = 'none';
                                            }}
                                        />
                                        <div className="absolute inset-0 flex items-center justify-center bg-black/0 transition-all group-hover:bg-black/10">
                                            <div className="rounded-full bg-white/90 p-2 opacity-0 shadow-sm transition-opacity group-hover:opacity-100">
                                                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                            </div>
                                        </div>
                                    </a>
                                ))}
                            </div>
                        )}
                    </section>
                )}

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

            </div>
        </AuthenticatedLayout>
    );
}

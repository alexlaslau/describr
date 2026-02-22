import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

const STEPS = ['Name', 'Links', 'Review'];

function StepIndicator({ currentStep }: { currentStep: number }) {
    return (
        <div className="flex items-center justify-center gap-2">
            {STEPS.map((label, i) => (
                <div key={label} className="flex items-center gap-2">
                    <div className="flex items-center gap-2.5">
                        <span
                            className={`flex h-9 w-9 items-center justify-center rounded-full text-sm font-semibold transition-all ${i < currentStep
                                ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/25'
                                : i === currentStep
                                    ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/25'
                                    : 'border-2 border-gray-200 bg-white text-gray-400'
                                }`}
                        >
                            {i < currentStep ? (
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                </svg>
                            ) : (
                                i + 1
                            )}
                        </span>
                        <span
                            className={`text-sm font-medium transition-colors ${i <= currentStep ? 'text-gray-900' : 'text-gray-400'
                                }`}
                        >
                            {label}
                        </span>
                    </div>
                    {i < STEPS.length - 1 && (
                        <div
                            className={`mx-3 h-px w-14 transition-colors ${i < currentStep ? 'bg-indigo-600' : 'bg-gray-200'
                                }`}
                        />
                    )}
                </div>
            ))}
        </div>
    );
}

function isValidUrl(urlString: string): boolean {
    try {
        const url = new URL(urlString);
        return url.protocol === 'http:' || url.protocol === 'https:';
    } catch {
        return false;
    }
}

export default function Create({ }: PageProps) {
    const { config } = usePage<PageProps>().props;
    const maxLinks = config.maxLinksPerProduct;
    const [step, setStep] = useState(0);
    const [name, setName] = useState('');
    const [links, setLinks] = useState<string[]>(['']);
    const [aiProvider, setAiProvider] = useState<'openai' | 'anthropic'>('openai');
    const [nameError, setNameError] = useState('');
    const [linkErrors, setLinkErrors] = useState<string[]>(['']);
    const [submitting, setSubmitting] = useState(false);

    function goToLinks() {
        if (!name.trim()) {
            setNameError('Product name is required.');
            return;
        }
        setNameError('');
        setStep(1);
    }

    function goToReview() {
        const errors = links.map((link) => {
            if (!link.trim()) return 'URL is required.';
            if (!isValidUrl(link.trim())) return 'Enter a valid URL (e.g. https://example.com).';
            return '';
        });

        if (errors.some((e) => e)) {
            setLinkErrors(errors);
            return;
        }

        setLinkErrors(links.map(() => ''));
        setStep(2);
    }

    function addLink() {
        if (links.length >= maxLinks) return;
        setLinks([...links, '']);
        setLinkErrors([...linkErrors, '']);
    }

    function removeLink(index: number) {
        if (links.length <= 1) return;
        setLinks(links.filter((_, i) => i !== index));
        setLinkErrors(linkErrors.filter((_, i) => i !== index));
    }

    function updateLink(index: number, value: string) {
        const newLinks = [...links];
        newLinks[index] = value;
        setLinks(newLinks);

        if (linkErrors[index]) {
            const newErrors = [...linkErrors];
            newErrors[index] = '';
            setLinkErrors(newErrors);
        }
    }

    function handleSubmit(e: FormEvent) {
        e.preventDefault();
        setSubmitting(true);
        router.post(route('products.store'), {
            name: name.trim(),
            links: links.map((l) => l.trim()),
            ai_provider: aiProvider,
        }, {
            onFinish: () => setSubmitting(false),
        });
    }

    return (
        <AuthenticatedLayout>
            <Head title="New Product" />

            <div className="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
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

                <h1 className="mt-8 text-3xl font-bold tracking-tight text-gray-900">
                    New Product
                </h1>

                {/* Step indicator */}
                <div className="mt-10">
                    <StepIndicator currentStep={step} />
                </div>

                {/* Steps */}
                <div className="mt-12">
                    {/* Step 1: Name */}
                    {step === 0 && (
                        <div className="overflow-hidden rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
                            <div className="space-y-6">
                                <div>
                                    <label htmlFor="product-name" className="block text-base font-semibold text-gray-800">
                                        Product Name
                                    </label>
                                    <p className="mt-1 text-sm text-gray-500">
                                        Give your product a descriptive name.
                                    </p>
                                    <input
                                        id="product-name"
                                        type="text"
                                        value={name}
                                        onChange={(e) => {
                                            setName(e.target.value);
                                            if (nameError) setNameError('');
                                        }}
                                        onKeyDown={(e) => e.key === 'Enter' && goToLinks()}
                                        placeholder="e.g. Red funny cat toy"
                                        className={`mt-3 block w-full rounded-xl border-2 bg-gray-50/50 px-5 py-3.5 text-base text-gray-900 placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all ${nameError
                                            ? 'border-red-300 focus:border-red-500 focus:ring-red-500/20'
                                            : 'border-gray-200'
                                            }`}
                                        autoFocus
                                    />
                                    {nameError && (
                                        <p className="mt-2.5 text-sm font-medium text-red-500">{nameError}</p>
                                    )}
                                </div>

                                <div className="flex justify-end pt-2">
                                    <button
                                        type="button"
                                        onClick={goToLinks}
                                        className="rounded-xl bg-indigo-600 px-8 py-3 text-base font-medium text-white shadow-sm shadow-indigo-600/20 transition-all hover:bg-indigo-700 hover:shadow-md hover:shadow-indigo-600/25 active:scale-[0.98]"
                                    >
                                        Next
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Step 2: Links */}
                    {step === 1 && (
                        <div className="overflow-hidden rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
                            <div className="space-y-6">
                                <div>
                                    <label className="block text-base font-semibold text-gray-800">
                                        Product URLs
                                    </label>
                                    <p className="mt-1 text-sm text-gray-500">
                                        Add links to pages containing product information.
                                    </p>
                                </div>

                                <div className="space-y-3">
                                    {links.map((link, index) => (
                                        <div key={index}>
                                            <div className="flex items-center gap-2">
                                                <div className="relative flex-1">
                                                    <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                            <path strokeLinecap="round" strokeLinejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                        </svg>
                                                    </div>
                                                    <input
                                                        type="url"
                                                        value={link}
                                                        onChange={(e) => updateLink(index, e.target.value)}
                                                        placeholder="https://example.com/product"
                                                        className={`block w-full rounded-xl border-2 bg-gray-50/50 py-3.5 pl-11 pr-5 text-base text-gray-900 placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all ${linkErrors[index]
                                                            ? 'border-red-300 focus:border-red-500 focus:ring-red-500/20'
                                                            : 'border-gray-200'
                                                            }`}
                                                        autoFocus={index === links.length - 1}
                                                    />
                                                </div>
                                                {links.length > 1 && (
                                                    <button
                                                        type="button"
                                                        onClick={() => removeLink(index)}
                                                        className="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl border border-transparent text-gray-300 transition-all hover:border-red-200 hover:bg-red-50 hover:text-red-500"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                                                        </svg>
                                                    </button>
                                                )}
                                            </div>
                                            {linkErrors[index] && (
                                                <p className="mt-1.5 text-sm font-medium text-red-500">{linkErrors[index]}</p>
                                            )}
                                        </div>
                                    ))}
                                </div>

                                <div className="flex items-center gap-3">
                                    <button
                                        type="button"
                                        onClick={addLink}
                                        disabled={links.length >= maxLinks}
                                        className="inline-flex items-center gap-2 rounded-lg border border-dashed border-gray-300 px-4 py-2.5 text-sm font-medium text-indigo-600 transition-all hover:border-indigo-300 hover:bg-indigo-50/50 hover:text-indigo-700 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-gray-300 disabled:hover:bg-transparent disabled:hover:text-indigo-600"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fillRule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clipRule="evenodd" />
                                        </svg>
                                        Add another link
                                    </button>
                                    <span className="text-xs text-gray-400">{links.length}/{maxLinks}</span>
                                </div>

                                <div className="flex justify-between pt-2">
                                    <button
                                        type="button"
                                        onClick={() => setStep(0)}
                                        className="rounded-xl border-2 border-gray-200 px-8 py-3 text-base font-medium text-gray-600 transition-all hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900"
                                    >
                                        Back
                                    </button>
                                    <button
                                        type="button"
                                        onClick={goToReview}
                                        className="rounded-xl bg-indigo-600 px-8 py-3 text-base font-medium text-white shadow-sm shadow-indigo-600/20 transition-all hover:bg-indigo-700 hover:shadow-md hover:shadow-indigo-600/25 active:scale-[0.98]"
                                    >
                                        Next
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Step 3: Review */}
                    {step === 2 && (
                        <div className="space-y-8">
                            <div className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                                <div className="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                                    <p className="text-xs font-semibold uppercase tracking-wider text-gray-400">
                                        Review Your Product
                                    </p>
                                </div>
                                <div className="p-6">
                                    <div>
                                        <p className="text-xs font-semibold uppercase tracking-wider text-gray-400">
                                            Product Name
                                        </p>
                                        <p className="mt-2 text-lg font-semibold text-gray-900">{name}</p>
                                    </div>

                                    <div className="mt-8">
                                        <p className="text-xs font-semibold uppercase tracking-wider text-gray-400">
                                            Links ({links.length})
                                        </p>
                                        <ul className="mt-3 space-y-2.5">
                                            {links.map((link, i) => (
                                                <li key={i} className="flex items-center gap-3 rounded-lg border border-gray-100 bg-gray-50/50 px-4 py-3 text-sm text-gray-600">
                                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                        <path strokeLinecap="round" strokeLinejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                    </svg>
                                                    <span className="truncate">{link}</span>
                                                </li>
                                            ))}
                                        </ul>
                                    </div>

                                    <div className="mt-8">
                                        <p className="text-xs font-semibold uppercase tracking-wider text-gray-400">
                                            AI Engine
                                        </p>
                                        <div className="mt-3 flex gap-3">
                                            {[
                                                { value: 'openai' as const, label: 'OpenAI', desc: 'GPT-4o mini · Cheaper, lower quality' },
                                                { value: 'anthropic' as const, label: 'Anthropic', desc: 'Claude Haiku 4.5 · Better quality, higher cost' },
                                            ].map((option) => (
                                                <label
                                                    key={option.value}
                                                    className={`flex flex-1 cursor-pointer items-center gap-3 rounded-xl border-2 px-5 py-4 transition-all ${aiProvider === option.value
                                                        ? 'border-indigo-500 bg-indigo-50/50 shadow-sm shadow-indigo-500/10'
                                                        : 'border-gray-200 bg-gray-50/30 hover:border-gray-300 hover:bg-gray-50'
                                                        }`}
                                                >
                                                    <input
                                                        type="radio"
                                                        name="ai_provider"
                                                        value={option.value}
                                                        checked={aiProvider === option.value}
                                                        onChange={() => setAiProvider(option.value)}
                                                        className="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                    />
                                                    <div>
                                                        <p className={`text-sm font-semibold ${aiProvider === option.value ? 'text-indigo-900' : 'text-gray-700'
                                                            }`}>
                                                            {option.label}
                                                        </p>
                                                        <p className="text-xs text-gray-500">{option.desc}</p>
                                                    </div>
                                                </label>
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="flex justify-between">
                                <button
                                    type="button"
                                    onClick={() => setStep(1)}
                                    className="rounded-xl border-2 border-gray-200 px-8 py-3 text-base font-medium text-gray-600 transition-all hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900"
                                >
                                    Back
                                </button>
                                <button
                                    type="button"
                                    onClick={handleSubmit}
                                    disabled={submitting}
                                    className="rounded-xl bg-indigo-600 px-8 py-3 text-base font-medium text-white shadow-sm shadow-indigo-600/20 transition-all hover:bg-indigo-700 hover:shadow-md hover:shadow-indigo-600/25 active:scale-[0.98] disabled:opacity-50 disabled:shadow-none"
                                >
                                    {submitting ? 'Creating…' : 'Create Product'}
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

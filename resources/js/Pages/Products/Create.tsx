import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

const STEPS = ['Name', 'Links', 'Review'];

function StepIndicator({ currentStep }: { currentStep: number }) {
    return (
        <div className="flex items-center justify-center gap-2">
            {STEPS.map((label, i) => (
                <div key={label} className="flex items-center gap-2">
                    <div className="flex items-center gap-2">
                        <span
                            className={`flex h-7 w-7 items-center justify-center rounded-full text-xs font-medium transition-colors ${i < currentStep
                                ? 'bg-indigo-600 text-white'
                                : i === currentStep
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-gray-100 text-gray-400'
                                }`}
                        >
                            {i < currentStep ? (
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                </svg>
                            ) : (
                                i + 1
                            )}
                        </span>
                        <span
                            className={`text-sm font-medium ${i <= currentStep ? 'text-gray-900' : 'text-gray-400'
                                }`}
                        >
                            {label}
                        </span>
                    </div>
                    {i < STEPS.length - 1 && (
                        <div
                            className={`mx-2 h-px w-12 ${i < currentStep ? 'bg-indigo-600' : 'bg-gray-200'
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
    const [step, setStep] = useState(0);
    const [name, setName] = useState('');
    const [links, setLinks] = useState<string[]>(['']);
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
        }, {
            onFinish: () => setSubmitting(false),
        });
    }

    return (
        <AuthenticatedLayout>
            <Head title="New Product" />

            <div className="mx-auto max-w-2xl px-4 py-10 sm:px-6 lg:px-8">
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

                <h1 className="mt-6 text-2xl font-semibold tracking-tight text-gray-900">
                    New Product
                </h1>

                {/* Step indicator */}
                <div className="mt-8">
                    <StepIndicator currentStep={step} />
                </div>

                {/* Steps */}
                <div className="mt-10">
                    {/* Step 1: Name */}
                    {step === 0 && (
                        <div className="space-y-6">
                            <div>
                                <label htmlFor="product-name" className="block text-sm font-medium text-gray-700">
                                    Product Name
                                </label>
                                <input
                                    id="product-name"
                                    type="text"
                                    value={name}
                                    onChange={(e) => {
                                        setName(e.target.value);
                                        if (nameError) setNameError('');
                                    }}
                                    onKeyDown={(e) => e.key === 'Enter' && goToLinks()}
                                    placeholder="e.g. Nike Air Max 90"
                                    className={`mt-2 block w-full rounded-lg border px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 transition ${nameError
                                        ? 'border-red-300 focus:ring-red-500'
                                        : 'border-gray-200'
                                        }`}
                                    autoFocus
                                />
                                {nameError && (
                                    <p className="mt-2 text-sm text-red-500">{nameError}</p>
                                )}
                            </div>

                            <div className="flex justify-end">
                                <button
                                    type="button"
                                    onClick={goToLinks}
                                    className="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700"
                                >
                                    Next
                                </button>
                            </div>
                        </div>
                    )}

                    {/* Step 2: Links */}
                    {step === 1 && (
                        <div className="space-y-6">
                            <div>
                                <label className="block text-sm font-medium text-gray-700">
                                    Product URLs
                                </label>
                                <p className="mt-1 text-sm text-gray-400">
                                    Add links to pages containing product information.
                                </p>
                            </div>

                            <div className="space-y-3">
                                {links.map((link, index) => (
                                    <div key={index}>
                                        <div className="flex items-center gap-2">
                                            <input
                                                type="url"
                                                value={link}
                                                onChange={(e) => updateLink(index, e.target.value)}
                                                placeholder="https://example.com/product"
                                                className={`block w-full rounded-lg border px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 transition ${linkErrors[index]
                                                    ? 'border-red-300 focus:ring-red-500'
                                                    : 'border-gray-200'
                                                    }`}
                                                autoFocus={index === links.length - 1}
                                            />
                                            {links.length > 1 && (
                                                <button
                                                    type="button"
                                                    onClick={() => removeLink(index)}
                                                    className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg text-gray-300 transition hover:bg-red-50 hover:text-red-500"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                                                    </svg>
                                                </button>
                                            )}
                                        </div>
                                        {linkErrors[index] && (
                                            <p className="mt-1 text-sm text-red-500">{linkErrors[index]}</p>
                                        )}
                                    </div>
                                ))}
                            </div>

                            <button
                                type="button"
                                onClick={addLink}
                                className="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 transition hover:text-indigo-700"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fillRule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clipRule="evenodd" />
                                </svg>
                                Add another link
                            </button>

                            <div className="flex justify-between">
                                <button
                                    type="button"
                                    onClick={() => setStep(0)}
                                    className="rounded-lg border border-gray-200 px-6 py-2.5 text-sm font-medium text-gray-600 transition hover:border-gray-300 hover:text-gray-900"
                                >
                                    Back
                                </button>
                                <button
                                    type="button"
                                    onClick={goToReview}
                                    className="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700"
                                >
                                    Next
                                </button>
                            </div>
                        </div>
                    )}

                    {/* Step 3: Review */}
                    {step === 2 && (
                        <div className="space-y-8">
                            <div className="rounded-xl border border-gray-100 p-6">
                                <div>
                                    <p className="text-xs font-medium uppercase tracking-wider text-gray-400">
                                        Product Name
                                    </p>
                                    <p className="mt-1 text-base font-medium text-gray-900">{name}</p>
                                </div>

                                <div className="mt-6">
                                    <p className="text-xs font-medium uppercase tracking-wider text-gray-400">
                                        Links ({links.length})
                                    </p>
                                    <ul className="mt-2 space-y-2">
                                        {links.map((link, i) => (
                                            <li key={i} className="flex items-center gap-2 text-sm text-gray-600">
                                                <span className="h-1.5 w-1.5 flex-shrink-0 rounded-full bg-gray-300" />
                                                <span className="truncate">{link}</span>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            </div>

                            <div className="flex justify-between">
                                <button
                                    type="button"
                                    onClick={() => setStep(1)}
                                    className="rounded-lg border border-gray-200 px-6 py-2.5 text-sm font-medium text-gray-600 transition hover:border-gray-300 hover:text-gray-900"
                                >
                                    Back
                                </button>
                                <button
                                    type="button"
                                    onClick={handleSubmit}
                                    disabled={submitting}
                                    className="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700 disabled:opacity-50"
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

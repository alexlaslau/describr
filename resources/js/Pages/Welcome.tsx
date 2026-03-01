import { PageProps } from '@/types';
import { Head, Link } from '@inertiajs/react';

type WelcomeProps = PageProps<{
    canLogin: boolean;
    canRegister: boolean;
    stats?: {
        total: number;
        completed: number;
        inProgress: number;
    };
}>;

export default function Welcome({ auth, canLogin, canRegister, stats }: WelcomeProps) {
    const isLoggedIn = !!auth.user;

    return (
        <>
            <Head title="Welcome" />
            <div className="min-h-screen bg-white">

                {/* Nav */}
                <nav className="flex items-center justify-between px-8 py-5 lg:px-16">
                    <img src="/favicon.png" alt="Describr" className="h-9 w-auto" />
                    <div className="flex items-center gap-3">
                        {isLoggedIn ? (
                            <Link
                                href={route('products.index')}
                                className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
                            >
                                Go to Dashboard
                            </Link>
                        ) : (
                            <>
                                {canLogin && (
                                    <Link
                                        href={route('login')}
                                        className="rounded-lg px-4 py-2 text-sm font-medium text-gray-500 transition hover:text-gray-900"
                                    >
                                        Log in
                                    </Link>
                                )}
                                {canRegister && (
                                    <Link
                                        href={route('register')}
                                        className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700"
                                    >
                                        Get Started
                                    </Link>
                                )}
                            </>
                        )}
                    </div>
                </nav>

                {/* Content */}
                <div className="mx-auto max-w-4xl px-8 py-4">
                    {isLoggedIn ? (
                        /* Logged-in state */
                        <div className="text-center">
                            <p className="text-sm font-medium uppercase tracking-widest text-indigo-600">
                                Welcome back
                            </p>
                            <h1 className="mt-3 text-4xl font-semibold tracking-tight text-gray-900">
                                {auth.user.name}
                            </h1>
                            <p className="mt-4 text-lg text-gray-500">
                                Here's a snapshot of your product descriptions.
                            </p>

                            {/* Stats */}
                            {stats && (
                                <div className="mt-12 grid grid-cols-3 divide-x divide-gray-100 rounded-xl border border-gray-100 bg-gray-50/50">
                                    <div className="px-6 py-8">
                                        <p className="text-3xl font-semibold text-gray-900">{stats.total}</p>
                                        <p className="mt-1 text-sm text-gray-500">Total Products</p>
                                    </div>
                                    <div className="px-6 py-8">
                                        <p className="text-3xl font-semibold text-green-600">{stats.completed}</p>
                                        <p className="mt-1 text-sm text-gray-500">Completed</p>
                                    </div>
                                    <div className="px-6 py-8">
                                        <p className="text-3xl font-semibold text-amber-600">{stats.inProgress}</p>
                                        <p className="mt-1 text-sm text-gray-500">In Progress</p>
                                    </div>
                                </div>
                            )}

                            <Link
                                href={route('products.index')}
                                className="mt-10 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-3 text-sm font-medium text-white transition hover:bg-indigo-700"
                            >
                                Go to Dashboard
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fillRule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clipRule="evenodd" />
                                </svg>
                            </Link>
                        </div>
                    ) : (
                        /* Guest state */
                        <div className="text-center">

                            {/* Hero logo */}
                            <div className="flex justify-center">
                                <img src="/logo.png" alt="Describr" className="h-24 w-auto opacity-90" />
                            </div>

                            {/* Badge */}
                            <div className="mt-10 inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-indigo-50 px-4 py-1.5">
                                <span className="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                                <span className="text-xs font-medium text-indigo-600 tracking-wide">AI-Powered · No writing needed</span>
                            </div>

                            {/* Headline */}
                            <h1 className="mt-6 text-5xl font-bold tracking-tight text-gray-900 sm:text-6xl leading-[1.1]">
                                Stop writing product<br />
                                <span className="text-indigo-600">descriptions by hand.</span>
                            </h1>

                            <p className="mx-auto mt-7 max-w-xl text-xl leading-relaxed text-gray-500">
                                Describr turns any product URL into a polished, conversion-ready description in under 30 seconds — so you can focus on selling, not writing.
                            </p>

                            {/* CTAs */}
                            <div className="mt-10 flex items-center justify-center gap-4">
                                {canRegister && (
                                    <Link
                                        href={route('register')}
                                        className="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-8 py-4 text-base font-semibold text-white shadow-md shadow-indigo-200 transition hover:bg-indigo-700 hover:shadow-indigo-300"
                                    >
                                        Try it free — no card needed
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fillRule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clipRule="evenodd" />
                                        </svg>
                                    </Link>
                                )}
                                {canLogin && (
                                    <Link
                                        href={route('login')}
                                        className="rounded-xl border border-gray-200 px-7 py-3.5 text-sm font-medium text-gray-600 transition hover:border-gray-300 hover:text-gray-900"
                                    >
                                        Log in
                                    </Link>
                                )}
                            </div>
                            <p className="mt-3 text-xs text-gray-400">Free to start · Takes 30 seconds to set up</p>

                            {/* Social proof numbers */}
                            <div className="mx-auto mt-14 grid max-w-sm grid-cols-3 gap-px overflow-hidden rounded-2xl border border-gray-100 bg-gray-100">
                                <div className="bg-white px-4 py-6">
                                    <p className="text-2xl font-bold text-gray-900">30s</p>
                                    <p className="mt-1 text-xs text-gray-500">avg. per description</p>
                                </div>
                                <div className="bg-white px-4 py-6">
                                    <p className="text-2xl font-bold text-gray-900">100%</p>
                                    <p className="mt-1 text-xs text-gray-500">free to start</p>
                                </div>
                                <div className="bg-white px-4 py-6">
                                    <p className="text-2xl font-bold text-gray-900">0</p>
                                    <p className="mt-1 text-xs text-gray-500">copywriters needed</p>
                                </div>
                            </div>

                            {/* Divider */}
                            <div className="mt-24 flex items-center gap-4">
                                <div className="h-px flex-1 bg-gray-100" />
                                <span className="text-xs font-medium uppercase tracking-widest text-gray-400">How it works</span>
                                <div className="h-px flex-1 bg-gray-100" />
                            </div>

                            {/* Feature highlights */}
                            <div className="mt-10 grid gap-6 text-left sm:grid-cols-3">
                                <div className="rounded-2xl border border-gray-100 bg-gray-50/60 p-7 transition hover:border-indigo-100 hover:bg-indigo-50/30">
                                    <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                        </svg>
                                    </div>
                                    <p className="mt-5 text-xs font-semibold uppercase tracking-widest text-indigo-500">Step 1</p>
                                    <h3 className="mt-1.5 text-base font-semibold text-gray-900">Paste any product URL</h3>
                                    <p className="mt-2 text-sm leading-relaxed text-gray-500">
                                        Works with any e-commerce site. Drop the link in and we'll pull everything we need automatically.
                                    </p>
                                </div>
                                <div className="rounded-2xl border border-gray-100 bg-gray-50/60 p-7 transition hover:border-indigo-100 hover:bg-indigo-50/30">
                                    <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                        </svg>
                                    </div>
                                    <p className="mt-5 text-xs font-semibold uppercase tracking-widest text-indigo-500">Step 2</p>
                                    <h3 className="mt-1.5 text-base font-semibold text-gray-900">AI does the heavy lifting</h3>
                                    <p className="mt-2 text-sm leading-relaxed text-gray-500">
                                        Our AI writes compelling, SEO-optimised copy that sells — in seconds, not hours.
                                    </p>
                                </div>
                                <div className="rounded-2xl border border-gray-100 bg-gray-50/60 p-7 transition hover:border-indigo-100 hover:bg-indigo-50/30">
                                    <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <p className="mt-5 text-xs font-semibold uppercase tracking-widest text-indigo-500">Step 3</p>
                                    <h3 className="mt-1.5 text-base font-semibold text-gray-900">Copy & go live</h3>
                                    <p className="mt-2 text-sm leading-relaxed text-gray-500">
                                        Ready to publish. No editing, no revisions, no freelancer invoices. Just great copy, instantly.
                                    </p>
                                </div>
                            </div>

                            {/* Bottom CTA */}
                            {canRegister && (
                                <div className="mt-20 rounded-2xl bg-indigo-600 px-8 py-12">
                                    <h2 className="text-2xl font-bold text-white">Ready to save hours every week?</h2>
                                    <p className="mt-3 text-indigo-200">Join and generate your first description in under a minute.</p>
                                    <Link
                                        href={route('register')}
                                        className="mt-7 inline-flex items-center gap-2 rounded-xl bg-white px-7 py-3.5 text-sm font-semibold text-indigo-600 shadow transition hover:bg-indigo-50"
                                    >
                                        Get started for free
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fillRule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clipRule="evenodd" />
                                        </svg>
                                    </Link>
                                </div>
                            )}
                        </div>
                    )}
                </div>

                {/* Footer */}
                <footer className="border-t border-gray-100 py-8 text-center">
                    <img src="/favicon.png" alt="Describr" className="mx-auto mb-3 h-6 w-auto opacity-40" />
                    <p className="text-xs text-gray-400">© {new Date().getFullYear()} Describr. All rights reserved.</p>
                </footer>
            </div>
        </>
    );
}

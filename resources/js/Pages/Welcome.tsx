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
                <nav className="flex items-center justify-between border-b border-gray-100 px-8 py-5 lg:px-16">
                    <img src="/favicon.png" alt="Describr" className="h-8 w-auto" />
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
                                        className="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 transition hover:text-gray-900"
                                    >
                                        Log in
                                    </Link>
                                )}
                                {canRegister && (
                                    <Link
                                        href={route('register')}
                                        className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
                                    >
                                        Get Started
                                    </Link>
                                )}
                            </>
                        )}
                    </div>
                </nav>

                {/* Content */}
                <div className="mx-auto max-w-3xl px-8 py-24 lg:py-32">
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
                            <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50">
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-7 w-7 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z" />
                                </svg>
                            </div>
                            <h1 className="mt-8 text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl">
                                AI-Powered Product Descriptions
                            </h1>
                            <p className="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-gray-500">
                                Drop your product links, and let AI craft compelling, conversion-ready descriptions in seconds. No writing required.
                            </p>
                            <div className="mt-10 flex items-center justify-center gap-4">
                                {canLogin && (
                                    <Link
                                        href={route('login')}
                                        className="rounded-lg border border-gray-200 px-6 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:text-gray-900"
                                    >
                                        Log in
                                    </Link>
                                )}
                                {canRegister && (
                                    <Link
                                        href={route('register')}
                                        className="rounded-lg bg-indigo-600 px-6 py-3 text-sm font-medium text-white transition hover:bg-indigo-700"
                                    >
                                        Get Started — Free
                                    </Link>
                                )}
                            </div>

                            {/* Feature highlights */}
                            <div className="mt-20 grid gap-8 text-left sm:grid-cols-3">
                                <div className="rounded-xl border border-gray-100 p-6">
                                    <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                        </svg>
                                    </div>
                                    <h3 className="mt-4 text-sm font-semibold text-gray-900">Paste Links</h3>
                                    <p className="mt-2 text-sm leading-relaxed text-gray-500">
                                        Add product URLs from any e-commerce site. We'll extract the details automatically.
                                    </p>
                                </div>
                                <div className="rounded-xl border border-gray-100 p-6">
                                    <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                        </svg>
                                    </div>
                                    <h3 className="mt-4 text-sm font-semibold text-gray-900">AI Generates</h3>
                                    <p className="mt-2 text-sm leading-relaxed text-gray-500">
                                        Our AI reads the scraped content and writes polished, SEO-friendly descriptions.
                                    </p>
                                </div>
                                <div className="rounded-xl border border-gray-100 p-6">
                                    <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <h3 className="mt-4 text-sm font-semibold text-gray-900">Ready to Use</h3>
                                    <p className="mt-2 text-sm leading-relaxed text-gray-500">
                                        Copy your descriptions and publish. It's that simple — no editing needed.
                                    </p>
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                {/* Footer */}
                <footer className="border-t border-gray-100 py-8 text-center text-xs text-gray-400">
                    Describr — AI Product Descriptions
                </footer>
            </div>
        </>
    );
}

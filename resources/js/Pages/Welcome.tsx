import { PageProps } from '@/types';
import { Head, Link } from '@inertiajs/react';
import LiveWorkflowDemo from '@/Components/LiveWorkflowDemo';

type WelcomeProps = PageProps<{
    canLogin: boolean;
    canRegister: boolean;
    stats?: {
        total: number;
        completed: number;
        inProgress: number;
    };
}>;

const howItWorks = [
    {
        title: 'Enter product name',
        description:
            'Start with the product name and context so the AI can identify angle, intent, and positioning.',
    },
    {
        title: 'Add competitor or supplier URLs',
        description:
            'Paste relevant pages. Describr scrapes multiple sources to feed the AI with richer product context.',
    },
    {
        title: 'Get AI-generated Romanian output',
        description:
            'Generate structured Romanian output with emotional copy, characteristics, and conversion-ready review text.',
    },
];

const features = [
    {
        title: 'AI writing engine',
        description: 'OpenAI or Anthropic models generate polished, sales-focused content from scraped source data.',
    },
    {
        title: 'Multi-source context',
        description: 'Feeds the model from competitor and supplier pages for more complete, grounded descriptions.',
    },
    {
        title: 'Romanian-native generation',
        description: 'Produces natural Romanian copy aligned with local e-commerce tone and expectations.',
    },
    {
        title: 'Instant publish-ready drafts',
        description: 'Go from URLs to AI-generated draft descriptions in minutes, not hours of manual writing.',
    },
];

export default function Welcome({ auth, canLogin, canRegister, stats }: WelcomeProps) {
    const isLoggedIn = !!auth.user;

    return (
        <>
            <Head title="Welcome" />

            <div className="min-h-screen relative overflow-hidden bg-gray-950 text-gray-100">
                <div className="pointer-events-none absolute inset-x-0 top-[-8rem] z-0 mx-auto h-[28rem] w-[90vw] max-w-6xl rounded-full bg-indigo-500/20 blur-3xl" />
                <div className="pointer-events-none absolute right-[-10rem] top-[16rem] z-0 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl" />

                <div className="relative z-10 mx-auto flex min-h-screen w-full max-w-7xl flex-col px-4 pb-10 pt-3 sm:px-8 sm:pt-6 lg:px-12">
                    <main className="mt-5 flex-1 sm:mt-10">
                        <section className="grid items-start gap-6 sm:gap-8 lg:grid-cols-12 lg:gap-10">
                            <div className="lg:col-span-7">
                                <img src="/logo.png" alt="Describr logo" className="mx-auto mb-3 h-auto w-[min(56vw,20.7rem)] sm:mb-4 sm:w-[min(41vw,20.7rem)]" />

                                <div className="inline-flex items-center gap-2 rounded-full border border-indigo-300/30 bg-indigo-300/10 px-3 py-1 text-[11px] font-medium uppercase tracking-[0.1em] text-indigo-200 sm:px-4 sm:py-1.5 sm:text-xs sm:tracking-[0.12em]">
                                    AI-Accelerated Marketing Copy
                                </div>

                                <h1 className="mt-4 max-w-3xl text-2xl font-semibold leading-tight text-white sm:mt-6 sm:text-5xl lg:text-6xl">
                                    AI that writes complete product descriptions for you.
                                </h1>

                                <p className="mt-4 max-w-2xl text-sm leading-relaxed text-gray-300 sm:mt-5 sm:text-lg">
                                    Describr uses scraped competitor and supplier data, then generates structured Romanian marketing copy automatically: emotional messaging, key characteristics, and review-ready sections.
                                </p>

                                <div className="mt-6 flex flex-col gap-3 sm:mt-8 sm:flex-row sm:flex-wrap sm:items-center">
                                    {isLoggedIn ? (
                                        <Link
                                            href={route('products.index')}
                                            className="inline-flex w-full justify-center rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white transition hover:translate-y-[-1px] hover:bg-indigo-700 sm:w-auto"
                                        >
                                            Go to Dashboard
                                        </Link>
                                    ) : (
                                        <>
                                            {canRegister && (
                                                <Link
                                                    href={route('register')}
                                                    className="inline-flex w-full justify-center rounded-xl bg-indigo-600 px-7 py-3.5 text-base font-semibold text-white transition hover:translate-y-[-1px] hover:bg-indigo-700 sm:w-auto"
                                                >
                                                    Get Started
                                                </Link>
                                            )}
                                            {canLogin && (
                                                <Link
                                                    href={route('login')}
                                                    className="inline-flex w-full justify-center rounded-xl border border-white/20 px-7 py-3.5 text-base font-medium text-gray-100 transition hover:border-white/40 hover:bg-white/10 sm:w-auto"
                                                >
                                                    Log in
                                                </Link>
                                            )}
                                        </>
                                    )}
                                </div>
                            </div>

                            <div className="lg:col-span-5 lg:pl-2">
                                {isLoggedIn ? (
                                    <div className="rounded-2xl border border-white/15 bg-white/5 p-4 shadow-2xl shadow-indigo-950/30 backdrop-blur-sm transition duration-300 hover:border-indigo-300/40 sm:rounded-3xl sm:p-6">
                                        <p className="text-xs uppercase tracking-[0.16em] text-gray-300">Welcome back</p>
                                        <h2 className="mt-2 break-words text-xl font-semibold text-white sm:text-2xl">{auth.user.name}</h2>
                                        <p className="mt-2 text-sm text-gray-300">Your current description pipeline.</p>

                                        <div className="mt-5 grid grid-cols-1 gap-2 text-center sm:mt-6 sm:grid-cols-3 sm:gap-3">
                                            <div className="rounded-xl border border-white/10 bg-white/5 px-3 py-4">
                                                <p className="text-2xl font-semibold text-white">{stats?.total ?? 0}</p>
                                                <p className="mt-1 text-xs text-gray-300">Total</p>
                                            </div>
                                            <div className="rounded-xl border border-emerald-300/20 bg-emerald-300/10 px-3 py-4">
                                                <p className="text-2xl font-semibold text-emerald-200">{stats?.completed ?? 0}</p>
                                                <p className="mt-1 text-xs text-emerald-100/90">Completed</p>
                                            </div>
                                            <div className="rounded-xl border border-amber-300/20 bg-amber-300/10 px-3 py-4">
                                                <p className="text-2xl font-semibold text-amber-200">{stats?.inProgress ?? 0}</p>
                                                <p className="mt-1 text-xs text-amber-100/90">In Progress</p>
                                            </div>
                                        </div>

                                        <Link
                                            href={route('products.index')}
                                            className="mt-6 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 transition hover:bg-gray-100 sm:w-auto"
                                        >
                                            Open Dashboard
                                        </Link>
                                    </div>
                                ) : (
                                    <LiveWorkflowDemo />
                                )}
                            </div>
                        </section>

                        <section className="mt-12 sm:mt-20">
                            <div className="mb-6 flex items-center gap-3 sm:mb-8">
                                <div className="h-px flex-1 bg-white/15" />
                                <p className="text-[11px] font-medium uppercase tracking-[0.16em] text-gray-400 sm:text-xs sm:tracking-[0.2em]">How It Works</p>
                                <div className="h-px flex-1 bg-white/15" />
                            </div>

                            <div className="grid gap-3 sm:gap-4 md:grid-cols-3">
                                {howItWorks.map((step, index) => (
                                    <article
                                        key={step.title}
                                        className="group rounded-2xl border border-white/10 bg-white/5 p-5 transition duration-300 hover:-translate-y-1 hover:border-indigo-300/40 hover:bg-white/10 sm:p-6"
                                    >
                                        <p className="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-200">Step {index + 1}</p>
                                        <h3 className="mt-2 text-lg font-semibold text-white">{step.title}</h3>
                                        <p className="mt-3 text-sm leading-relaxed text-gray-300">{step.description}</p>
                                    </article>
                                ))}
                            </div>
                        </section>

                        <section className="mt-10 sm:mt-16">
                            <div className="mb-5 flex items-center justify-between gap-6 sm:mb-8">
                                <div>
                                    <p className="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 sm:text-xs sm:tracking-[0.18em]">Feature Highlights</p>
                                    <h2 className="mt-2 text-lg font-semibold text-white sm:text-3xl">
                                        Built for fast e-commerce content operations
                                    </h2>
                                </div>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                {features.map((feature) => (
                                    <article
                                        key={feature.title}
                                        className="rounded-2xl border border-white/10 bg-white/[0.04] p-5 transition duration-300 hover:border-indigo-300/40 hover:bg-white/10 sm:p-6"
                                    >
                                        <h3 className="text-base font-semibold text-white">{feature.title}</h3>
                                        <p className="mt-2 text-sm leading-relaxed text-gray-300">{feature.description}</p>
                                    </article>
                                ))}
                            </div>
                        </section>
                    </main>

                    <footer className="mt-12 border-t border-white/10 pt-6 text-sm text-gray-400 sm:mt-16">
                        <div className="flex flex-col items-start justify-between gap-2 sm:flex-row sm:items-center">
                            <p>© {new Date().getFullYear()} Describr. AI-powered product descriptions.</p>
                            <p className="text-xs uppercase tracking-[0.14em] text-gray-500">Laravel + React + Tailwind + Inertia</p>
                        </div>
                    </footer>
                </div>
            </div>
        </>
    );
}

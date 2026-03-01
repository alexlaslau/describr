import { useEffect, useMemo, useState } from 'react';

type UrlStatus = 'idle' | 'loading' | 'done';

export default function LiveWorkflowDemo() {
    const productName = 'Espressor Smart Brew X9';
    const urlSources = useMemo(
        () => [
            'supplier.ro/products/smart-brew-x9',
            'competitor-shop.ro/espressor-x9-pro',
            'home-market.ro/catalog/smart-brew-x9',
        ],
        [],
    );
    const generatedWords = useMemo(
        () =>
            'Smart Brew X9 aduce gust intens si consistenta premium in fiecare dimineata. Designul modern, controlul intuitiv si extractia precisa transforma rutina cafelei intr-un ritual rapid si elegant. Recomandat pentru clienti care cauta performanta stabila, aroma bogata si experienta de barista acasa.'
                .split(' '),
        [],
    );

    const [typedLength, setTypedLength] = useState(0);
    const [urlStatuses, setUrlStatuses] = useState<UrlStatus[]>(() => urlSources.map(() => 'idle'));
    const [visibleWords, setVisibleWords] = useState(0);
    const [activeStep, setActiveStep] = useState<1 | 2 | 3 | 4>(1);
    const [completionPulse, setCompletionPulse] = useState(false);

    useEffect(() => {
        let cancelled = false;
        let timer: ReturnType<typeof setTimeout> | null = null;

        const wait = (ms: number) =>
            new Promise<void>((resolve) => {
                timer = setTimeout(resolve, ms);
            });

        const loop = async () => {
            while (!cancelled) {
                setActiveStep(1);
                setTypedLength(0);
                setUrlStatuses(urlSources.map(() => 'idle'));
                setVisibleWords(0);
                setCompletionPulse(false);
                await wait(350);

                for (let i = 1; i <= productName.length && !cancelled; i++) {
                    setTypedLength(i);
                    await wait(52);
                }

                await wait(500);
                setActiveStep(2);

                for (let i = 0; i < urlSources.length && !cancelled; i++) {
                    setUrlStatuses((prev) =>
                        prev.map((state, index) => {
                            if (index < i) return 'done';
                            if (index === i) return 'loading';
                            return state;
                        }),
                    );
                    await wait(760);
                    setUrlStatuses((prev) => prev.map((state, index) => (index === i ? 'done' : state)));
                    await wait(220);
                }

                await wait(300);
                setActiveStep(3);

                for (let i = 1; i <= generatedWords.length && !cancelled; i++) {
                    setVisibleWords(i);
                    await wait(i % 7 === 0 ? 170 : 110);
                }

                await wait(450);
                setActiveStep(4);
                setCompletionPulse(true);
                await wait(1300);
                setCompletionPulse(false);
                await wait(700);
            }
        };

        void loop();

        return () => {
            cancelled = true;
            if (timer) clearTimeout(timer);
        };
    }, [generatedWords.length, productName.length, urlSources]);

    const progressPercent =
        activeStep === 1
            ? (typedLength / productName.length) * 34
            : activeStep === 2
              ? 34 + (urlStatuses.filter((status) => status === 'done').length / urlSources.length) * 33
              : activeStep === 3
                ? 67 + (visibleWords / generatedWords.length) * 28
                : 100;

    const streamedText = generatedWords.slice(0, visibleWords).join(' ');

    const stepClass = (stepNumber: 1 | 2 | 3) => {
        const isActive = activeStep === stepNumber;
        const isDone = activeStep > stepNumber;
        return `rounded-2xl border p-4 transition-all duration-500 ${
            isActive
                ? 'border-indigo-300/50 bg-indigo-300/10 shadow-lg shadow-indigo-500/20'
                : isDone
                  ? 'border-emerald-300/30 bg-emerald-300/10'
                  : 'border-white/10 bg-white/5'
        }`;
    };

    return (
        <div className="rounded-2xl border border-white/15 bg-white/5 p-4 shadow-2xl shadow-indigo-950/30 backdrop-blur-sm sm:rounded-3xl sm:p-6">
            <p className="text-xs uppercase tracking-[0.16em] text-gray-300">Live Workflow</p>

            <div className="relative mt-4 space-y-3 sm:mt-5 sm:space-y-4">
                <div className="absolute left-3.5 top-5 h-[calc(100%-4rem)] w-px bg-white/10 sm:left-4">
                    <div
                        className="w-full bg-indigo-300 transition-all duration-500"
                        style={{ height: `${Math.max(0, Math.min(100, progressPercent))}%` }}
                    />
                </div>

                <div className="relative pl-8 sm:pl-10">
                    <span
                        className={`absolute left-[5px] top-5 h-3 w-3 rounded-full ring-4 ring-gray-950 sm:left-[7px] ${
                            activeStep >= 1 ? 'bg-indigo-300' : 'bg-white/30'
                        }`}
                    />
                    <div className={stepClass(1)}>
                        <p className="text-[11px] uppercase tracking-[0.16em] text-gray-300">Step 1 · Product</p>
                        <div className="mt-2 rounded-lg border border-white/10 bg-indigo-950/40 px-3 py-2 font-mono text-xs text-gray-100 sm:text-sm">
                            {productName.slice(0, typedLength)}
                            <span className={activeStep === 1 ? 'animate-pulse text-indigo-200' : 'text-transparent'}>|</span>
                        </div>
                    </div>
                </div>

                <div className="relative pl-8 sm:pl-10">
                    <span
                        className={`absolute left-[5px] top-5 h-3 w-3 rounded-full ring-4 ring-gray-950 sm:left-[7px] ${
                            activeStep >= 2 ? 'bg-indigo-300' : 'bg-white/30'
                        }`}
                    />
                    <div className={stepClass(2)}>
                        <p className="text-[11px] uppercase tracking-[0.16em] text-gray-300">Step 2 · Sources</p>
                        <div className="mt-2 space-y-2">
                            {urlSources.map((url, index) => {
                                const status = urlStatuses[index];
                                return (
                                    <div
                                        key={url}
                                        className="flex items-center justify-between gap-2 rounded-lg border border-white/10 bg-black/20 px-3 py-2 text-[11px] text-gray-200 sm:text-xs"
                                    >
                                        <span className="min-w-0 truncate pr-1">{url}</span>
                                        {status === 'loading' && (
                                            <span className="inline-flex shrink-0 items-center gap-1.5 text-indigo-100">
                                                <span className="h-3 w-3 animate-spin rounded-full border-2 border-indigo-200/30 border-t-indigo-200" />
                                                Parsing
                                            </span>
                                        )}
                                        {status === 'done' && (
                                            <span className="inline-flex shrink-0 items-center gap-1 text-emerald-200">
                                                <svg className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path
                                                        fillRule="evenodd"
                                                        d="M16.704 5.29a1 1 0 010 1.42l-7.25 7.25a1 1 0 01-1.415 0l-3.25-3.25a1 1 0 011.414-1.415l2.543 2.543 6.543-6.543a1 1 0 011.415 0z"
                                                        clipRule="evenodd"
                                                    />
                                                </svg>
                                                Done
                                            </span>
                                        )}
                                        {status === 'idle' && <span className="shrink-0 text-gray-500">Queued</span>}
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>

                <div className="relative pl-8 sm:pl-10">
                    <span
                        className={`absolute left-[5px] top-5 h-3 w-3 rounded-full ring-4 ring-gray-950 sm:left-[7px] ${
                            activeStep >= 3 ? 'bg-indigo-300' : 'bg-white/30'
                        }`}
                    />
                    <div className={stepClass(3)}>
                        <p className="text-[11px] uppercase tracking-[0.16em] text-gray-300">Step 3 · AI Generation</p>
                        <div className="mt-2 rounded-lg border border-indigo-300/25 bg-indigo-300/10 px-3 py-3 text-xs leading-relaxed text-indigo-100 sm:text-sm">
                            {streamedText}
                            <span className={activeStep === 3 ? 'ml-1 animate-pulse text-indigo-200' : 'text-transparent'}>|</span>
                        </div>
                    </div>
                </div>
            </div>

            <div
                className={`mt-4 rounded-xl border px-4 py-3 text-xs transition-all duration-500 sm:mt-5 sm:text-sm ${
                    activeStep === 4
                        ? 'border-emerald-300/40 bg-emerald-300/15 text-emerald-100'
                        : 'border-white/10 bg-white/5 text-gray-400'
                }`}
            >
                <div className="flex items-center gap-2">
                    <span className="relative inline-flex h-2.5 w-2.5">
                        <span
                            className={`absolute inline-flex h-full w-full rounded-full bg-emerald-300 ${
                                completionPulse ? 'animate-ping' : ''
                            }`}
                        />
                        <span className="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-300" />
                    </span>
                    <span className="font-medium">
                        {activeStep === 4 ? 'Done. Description generated.' : 'Running automated workflow...'}
                    </span>
                </div>
                <p className="mt-1 text-[11px] text-emerald-100/90 sm:text-xs">Output ready in under a minute, then loop restarts.</p>
            </div>
        </div>
    );
}

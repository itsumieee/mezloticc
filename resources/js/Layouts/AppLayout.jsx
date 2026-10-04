import { Head, Link, usePage } from '@inertiajs/react';
import { motion, useReducedMotion } from 'framer-motion';
import { lazy, Suspense, useEffect, useRef, useState } from 'react';
import CopyButton from '../Components/CopyButton';
import SiteHeader from '../Components/SiteHeader';
import { initMotion } from '../motion';

const GlobalScene = lazy(() => import('../Components/GlobalScene'));

const dashboardLinks = [
    ['', 'Overview'], ['/profile', 'Profile'], ['/inventory', 'Inventory'],
    ['/limited', 'Limited'], ['/animations', 'Animations'], ['/avatar', 'Avatar'],
    ['/bundles', 'Bundles'], ['/statistics', 'Statistics'], ['/value', 'Value'],
];

export default function AppLayout({ title, children }) {
    const { url, props } = usePage();
    const currentPath = url.split('?')[0];
    const isMlbb = currentPath === '/mlbb';
    const isGameHub = currentPath === '/';
    const hasOwnMarquee = currentPath === '/roblox';
    const userId = Number(url.match(/dashboard\/(\d+)/)?.[1] ?? 0);
    const isDashboard = userId > 0;
    const reduceMotion = useReducedMotion();
    const [scrollProgress, setScrollProgress] = useState(0);
    const [toast, setToast] = useState(null);
    const cursorRef = useRef(null);

    useEffect(() => {
        initMotion();
        let frame = 0;
        let attempts = 0;
        const resizeLenis = () => {
            if (frame) return;
            frame = window.requestAnimationFrame(() => {
                frame = 0;
                const lenis = window.__profileLens;
                if (!lenis) return;

                lenis.resize();
                if (lenis.dimensions.scrollHeight !== document.documentElement.scrollHeight && attempts < 30) {
                    attempts += 1;
                    resizeLenis();
                } else {
                    attempts = 0;
                }
            });
        };
        const layoutObserver = new ResizeObserver(resizeLenis);
        layoutObserver.observe(document.documentElement);
        const layout = document.querySelector('.app-frame');
        const content = layout?.querySelector('.page-main');

        if (layout) layoutObserver.observe(layout);
        if (content) layoutObserver.observe(content);
        resizeLenis();
        document.fonts?.ready.then(resizeLenis);
        return () => {
            layoutObserver.disconnect();
            if (frame) window.cancelAnimationFrame(frame);
        };
    }, [url]);

    useEffect(() => {
        const update = () => {
            const limit = document.documentElement.scrollHeight - window.innerHeight;
            setScrollProgress(limit > 0 ? (window.scrollY / limit) * 100 : 0);
        };

        window.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();
        return () => {
            window.removeEventListener('scroll', update);
            window.removeEventListener('resize', update);
        };
    }, []);

    useEffect(() => {
        const showToast = (event) => {
            setToast(event.detail);
            window.setTimeout(() => setToast(null), 2200);
        };
        window.addEventListener('app:toast', showToast);
        return () => window.removeEventListener('app:toast', showToast);
    }, []);

    useEffect(() => {
        const finePointer = window.matchMedia('(pointer: fine) and (min-width: 768px)');
        const moveCursor = (event) => {
            const cursor = cursorRef.current;
            if (!cursor) return;

            cursor.classList.add('is-visible');
            cursor.style.opacity = '1';
            cursor.style.transform = `translate3d(${event.clientX}px, ${event.clientY}px, 0) translate(-50%, -50%)`;
        };
        let active = false;
        const syncCursor = () => {
            const shouldBeActive = finePointer.matches;
            if (active === shouldBeActive) return;

            active = shouldBeActive;
            document.body.classList.toggle('has-custom-cursor', active);
            cursorRef.current?.classList.remove('is-visible');
            if (cursorRef.current) cursorRef.current.style.opacity = '0';
            if (active) window.addEventListener('pointermove', moveCursor, { passive: true });
            else window.removeEventListener('pointermove', moveCursor);
        };
        syncCursor();
        finePointer.addEventListener('change', syncCursor);

        return () => {
            document.body.classList.remove('has-custom-cursor');
            finePointer.removeEventListener('change', syncCursor);
            window.removeEventListener('pointermove', moveCursor);
        };
    }, []);

    return (
        <>
            <Head title={title ? `${title} · ${isGameHub || isMlbb ? 'Profile Index' : 'Roblox Account Checker'}` : 'Roblox Account Checker'}>
                <meta name="description" content={isGameHub
                    ? 'Pilih game untuk membuka halaman pemeriksaan akun. Roblox dan Mobile Legends tersedia; game lain akan ditambahkan bertahap.'
                    : isMlbb
                    ? 'Preview a Mobile Legends account report by player ID and server ID. MLBB data is unavailable until a source API is connected.'
                    : 'Inspect public Roblox account data — profile, inventory, limited items, animations and bundles. No credentials requested.'} />
                <meta name="theme-color" content="#edf3f6" />
            </Head>
            <svg className="glass-filter-defs" aria-hidden="true" focusable="false">
                <filter id="liquid-glass-edge" x="-5%" y="-5%" width="110%" height="110%">
                    <feTurbulence type="fractalNoise" baseFrequency="0.025" numOctaves="1" seed="8" />
                    <feDisplacementMap in="SourceGraphic" scale="2" />
                </filter>
            </svg>
            <div ref={cursorRef} className="chrome-cursor" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <defs><linearGradient id="cursor-chrome" x1="0" y1="0" x2="0" y2="1"><stop stopColor="#526a74" /><stop offset=".35" stopColor="#fff" /><stop offset=".58" stopColor="#00c8df" /><stop offset="1" stopColor="#526a74" /></linearGradient></defs>
                    <path d="M12 0.8 14.9 9.1 23.2 12l-8.3 2.9L12 23.2l-2.9-8.3L.8 12l8.3-2.9L12 .8Z" />
                </svg>
            </div>
            <Suspense fallback={<div className="scene-static" aria-hidden="true" />}>
                <GlobalScene reducedMotion={reduceMotion} />
            </Suspense>
            <div className="scroll-progress" style={{ transform: `scaleY(${scrollProgress / 100})` }} />
            <SiteHeader />
            <div className={`app-frame ${isDashboard ? 'has-sidebar' : ''}`}>
                {isDashboard && (
                    <aside className="dashboard-sidebar">
                        <p className="eyebrow">Index / User {userId}</p>
                        <nav aria-label="Profile sections">
                            {dashboardLinks.map(([path, label], index) => {
                                const target = `/dashboard/${userId}${path}`;
                                const active = url.split('?')[0] === target || (path === '' && url.split('?')[0] === `/dashboard/${userId}`);
                                return (
                                    <Link key={path} href={target} className={active ? 'active' : ''}>
                                        <span>{String(index + 1).padStart(2, '0')}</span>{label}<b aria-hidden="true">↗</b>
                                    </Link>
                                );
                            })}
                        </nav>
                        <div className="sidebar-account"><span>User ID</span><CopyButton value={userId} /><span>Mode</span><strong>Public</strong></div>
                    </aside>
                )}
                <main className="page-main">
                    <motion.div
                        key={url.split('?')[0]}
                        initial={reduceMotion ? false : { opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: reduceMotion ? 0 : 0.35, ease: 'easeOut' }}
                    >
                        {children}
                    </motion.div>
                </main>
            </div>
            {!hasOwnMarquee && (
                <div className="site-marquee" aria-label="Profile Index, public game data">
                    <div className="site-marquee-track"><span>PROFILE INDEX · PUBLIC GAME DATA · DIGITAL COLLECTIONS · PROFILE INDEX · PUBLIC GAME DATA · DIGITAL COLLECTIONS ·</span></div>
                </div>
            )}
            {props.flash?.status && <div className="toast toast-success" role="status">{props.flash.status}</div>}
            {props.flash?.error && <div className="toast toast-error" role="alert">{props.flash.error}</div>}
            {toast && <div className={`toast toast-${toast.kind}`} role="status">{toast.message}</div>}
        </>
    );
}

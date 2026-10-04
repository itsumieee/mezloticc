import { useEffect, useMemo, useState } from 'react';
import { Link, router, useForm, usePage } from '@inertiajs/react';

export default function SiteHeader() {
    const { url } = usePage();
    const currentPath = url.split('?')[0];
    const isGameHub = currentPath === '/';
    const isMlbb = currentPath === '/mlbb';
    const [hidden, setHidden] = useState(false);
    const [searchOpen, setSearchOpen] = useState(false);
    const [menuOpen, setMenuOpen] = useState(false);
    const [shortcutsOpen, setShortcutsOpen] = useState(false);
    const [navQuery, setNavQuery] = useState('');
    const search = useForm({ username: '' });
    const userId = Number(url.match(/dashboard\/(\d+)/)?.[1] ?? 0);

    useEffect(() => {
        let previousY = window.scrollY;
        const onScroll = () => {
            const nextY = window.scrollY;
            setHidden(nextY > 96 && nextY > previousY);
            previousY = nextY;
        };

        const onKeyDown = (event) => {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                setSearchOpen(true);
            }
            if (event.key === 'Escape') {
                setSearchOpen(false);
                setMenuOpen(false);
                setShortcutsOpen(false);
            }
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) return;
            if (event.key === '?') {
                event.preventDefault();
                setShortcutsOpen(true);
            }
            if (event.key === '/') {
                const input = document.getElementById('username') || document.getElementById('global-search');
                if (input) {
                    event.preventDefault();
                    input.focus();
                }
            }
            if (event.key.toLowerCase() === 'g') {
                window.__profileIndexKeyAt = Date.now();
            } else if (Date.now() - (window.__profileIndexKeyAt ?? 0) < 800) {
                const target = {
                    h: '/',
                    i: userId ? `/dashboard/${userId}/inventory` : null,
                    l: userId ? `/dashboard/${userId}/limited` : null,
                }[event.key.toLowerCase()];
                if (target) window.location.assign(target);
                window.__profileIndexKeyAt = 0;
            }
        };

        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('keydown', onKeyDown);
        return () => {
            window.removeEventListener('scroll', onScroll);
            window.removeEventListener('keydown', onKeyDown);
        };
    }, [userId]);

    const navItems = useMemo(() => [
        ['Game index', '/'], ['Roblox', '/roblox'], ['Mobile Legends', '/mlbb'],
        ['Compare', '/compare'], ['Watchlist', '/watchlist'],
        ['Recent searches', '/history'], ['Public API', '/api-docs'],
        ...(userId ? dashboardLinks.map(([, label, path]) => [label, `/dashboard/${userId}${path}`]) : []),
    ].filter(([label]) => label.toLowerCase().includes(navQuery.toLowerCase())), [navQuery, userId]);

    const submitSearch = (event) => {
        event.preventDefault();
        const pageMatch = navItems.find(([label]) => label.toLowerCase() === search.data.username.trim().toLowerCase());
        if (pageMatch) {
            router.visit(pageMatch[1]);
            setSearchOpen(false);
            return;
        }
        search.post('/search', { onSuccess: () => setSearchOpen(false) });
    };

    return (
        <>
            <header className={`site-header ${isGameHub ? 'is-game-hub' : ''} ${hidden ? 'is-hidden' : ''}`} style={{ backdropFilter: 'blur(22px) saturate(150%)', WebkitBackdropFilter: 'blur(22px) saturate(150%)' }}>
                <button className="menu-toggle" onClick={() => setMenuOpen(true)} aria-label="Open navigation">☰</button>
                <Link href="/" className="brand-lockup">
                    <span className="brand-mark" aria-hidden="true" />
                    <span>PROFILE<span className="brand-slash">/</span>INDEX</span>
                </Link>
                <div className="nav-context"><span className="status-dot" />{isGameHub ? 'GAME INDEX / SELECT A TITLE' : isMlbb ? 'MOBILE LEGENDS / DATA PREVIEW' : 'PUBLIC ROBLOX DATA'}</div>
                <nav className="header-links" aria-label="Main navigation">
                    {isGameHub ? (
                        <>
                            <Link href="/roblox">Roblox</Link>
                            <Link href="/mlbb">Mobile Legends</Link>
                        </>
                    ) : (
                        <>
                            <Link href="/">All games</Link>
                            <Link href="/roblox">Roblox</Link>
                            <Link href="/mlbb">MLBB</Link>
                            {!isMlbb && <>
                                <button className="header-search" onClick={() => setSearchOpen(true)}>⌕ <span>Search</span><kbd>Ctrl K</kbd></button>
                                <Link href="/compare">Compare</Link>
                                <Link href="/watchlist">Watchlist</Link>
                                <button className="shortcuts-trigger" onClick={() => setShortcutsOpen(true)} aria-label="Keyboard shortcuts">?</button>
                            </>}
                        </>
                    )}
                    {!isGameHub && <Link className="new-check" href="/roblox">New lookup <span aria-hidden="true">↗</span></Link>}
                </nav>
            </header>

            {menuOpen && (
                <div className="mobile-drawer-backdrop" onMouseDown={(event) => event.target === event.currentTarget && setMenuOpen(false)}>
                    <nav className="mobile-drawer" aria-label="Dashboard navigation">
                        <button className="drawer-close" onClick={() => setMenuOpen(false)}>Close ×</button>
                        {userId > 0 && dashboardLinks.map(([path, label], index) => (
                            <Link key={path} href={`/dashboard/${userId}${path}`} onClick={() => setMenuOpen(false)}>
                                <span>{String(index + 1).padStart(2, '0')}</span>{label}
                            </Link>
                        ))}
                        <Link href="/" onClick={() => setMenuOpen(false)}>All games</Link>
                        <Link href="/roblox" onClick={() => setMenuOpen(false)}>Roblox</Link>
                        <Link href="/mlbb" onClick={() => setMenuOpen(false)}>Mobile Legends</Link>
                        {!isMlbb && <><Link href="/history">Search history</Link><Link href="/api-docs">Public API</Link></>}
                    </nav>
                </div>
            )}

            {searchOpen && (
                <div className="command-backdrop" onMouseDown={(event) => event.target === event.currentTarget && setSearchOpen(false)}>
                    <form className="command-panel" onSubmit={submitSearch}>
                        <label htmlFor="global-search">Public profile lookup</label>
                        <div className="command-input-row">
                            <input
                                autoFocus
                                id="global-search"
                                value={search.data.username}
                                onChange={(event) => {
                                    search.setData('username', event.target.value);
                                    setNavQuery(event.target.value);
                                }}
                                placeholder="Username or numeric user ID"
                                maxLength="50"
                            />
                            <button disabled={search.processing}>{search.processing ? 'Checking…' : 'Lookup →'}</button>
                        </div>
                        {search.errors.username && <p className="field-error">{search.errors.username}</p>}
                        <nav className="command-results" aria-label="Pages">
                            {navItems.map(([label, href]) => <Link href={href} key={`${label}-${href}`} onClick={() => setSearchOpen(false)}>{label}<span>↗</span></Link>)}
                        </nav>
                        <p>Public Roblox data only · No credentials requested</p>
                    </form>
                </div>
            )}
            {shortcutsOpen && (
                <div className="command-backdrop" onMouseDown={(event) => event.target === event.currentTarget && setShortcutsOpen(false)}>
                    <section className="shortcuts-panel glass-panel" role="dialog" aria-modal="true" aria-labelledby="shortcuts-title">
                        <header><span className="eyebrow">Keyboard</span><button onClick={() => setShortcutsOpen(false)} aria-label="Close shortcuts">×</button></header>
                        <h2 id="shortcuts-title">Shortcuts</h2>
                        {[['Ctrl K', 'Open command palette'], ['/', 'Focus lookup'], ['Esc', 'Close dialog'], ['G then H', 'Go home'], ['G then I', 'Go to inventory'], ['G then L', 'Go to Limited'], ['?', 'Show shortcuts']].map(([key, description]) => <div className="shortcut-row" key={key}><span>{description}</span><kbd>{key}</kbd></div>)}
                    </section>
                </div>
            )}
        </>
    );
}

const dashboardLinks = [
    ['', 'Overview', ''], ['/profile', 'Profile', '/profile'], ['/inventory', 'Inventory', '/inventory'],
    ['/limited', 'Limited', '/limited'], ['/animations', 'Animations', '/animations'], ['/avatar', 'Avatar', '/avatar'],
    ['/bundles', 'Bundles', '/bundles'], ['/statistics', 'Statistics', '/statistics'], ['/value', 'Value', '/value'],
];

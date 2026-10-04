import { Link, usePage } from '@inertiajs/react';
import { motion, useReducedMotion } from 'framer-motion';
import AppLayout from '../Layouts/AppLayout';
import SearchBar from '../Components/SearchBar';
import { GlassPanel, Notice } from '../Components/GlassPanel';

const discoveryLinks = [
    ['01', 'Profile', 'Public account details, presence, creator activity and communities.'],
    ['02', 'Inventory', 'Public catalog items, limiteds, animations and bundles.'],
    ['03', 'Compare', 'Read two public profiles side by side.', '/compare'],
    ['04', 'Mobile Legends', 'Preview an account report for player ID and server ID.', '/mlbb'],
];

export default function Home() {
    const { props } = usePage();
    const reduceMotion = useReducedMotion();
    const words = ['Find', 'a', 'Roblox', 'profile'];

    return (
        <AppLayout title="Public profile lookup">
            <div className="home-page">
                <div className="home-topline"><span>Roblox Account Checker</span><i /><span>Public profile lookup</span></div>

                <section className="home-hero">
                    <div className="home-copy">
                        <p className="eyebrow"><span className="status-dot" />PUBLIC ROBLOX DATA</p>
                        <h1>{words.map((word, index) => (
                            <motion.span
                                className={index === 3 ? 'display-serif' : ''}
                                key={word}
                                initial={reduceMotion ? false : { opacity: 0, y: 35 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ delay: index * 0.11, duration: 0.65, ease: [0.22, 1, 0.36, 1] }}
                            >{word}{index < words.length - 1 ? ' ' : ''}</motion.span>
                        ))}</h1>
                        <p className="home-description">Look up a username to explore its public profile, avatar, inventory and collectibles.</p>
                        <Notice kind="error">{props.flash?.error || props.errors?.username}</Notice>
                        <SearchBar initialValue={props.query ?? ''} />
                    </div>

                    <GlassPanel as="aside" className="privacy-card">
                        <span className="eyebrow">Privacy first</span>
                        <p>No password, cookie or account access needed. Only public Roblox data is checked.</p>
                        <div className="privacy-facts"><span><b>01</b>Username needed</span><span><b>0</b>Credentials stored</span></div>
                    </GlassPanel>
                </section>

                <section className="home-scope-stage" aria-labelledby="scope-title">
                    <div className="home-scope-sticky glass-panel">
                        <div className="scope-copy">
                            <p className="eyebrow"><span className="status-dot" />DATA BOUNDARY / PUBLIC ONLY</p>
                            <h2 id="scope-title">Visible signals.<br /><span>Nothing private.</span></h2>
                            <p>Profile details, avatar renders, inventory, collectibles and creator activity are read only when Roblox makes them public. A private inventory stays private.</p>
                        </div>
                        <div className="scope-list">
                            <div><span>01</span><p>Profile & presence</p><small>Public account details and recent status</small></div>
                            <div><span>02</span><p>Items & avatar</p><small>Inventory visibility, wearing, limiteds, animations</small></div>
                            <div><span>03</span><p>Created & collected</p><small>Public experiences, communities and bundles</small></div>
                        </div>
                    </div>
                </section>

                <section className="home-discovery" aria-labelledby="discovery-title">
                    <div className="section-heading"><span>INDEX</span><h2 id="discovery-title">A closer look at public profiles</h2><i /><span>TOOLS</span></div>
                    <div className="discovery-grid">
                        {discoveryLinks.map(([number, title, description, href]) => {
                            const content = <>
                                    <span className="eyebrow">{number} / PUBLIC DATA</span>
                                    <h3>{title}</h3><p>{description}</p><span className="discovery-arrow" aria-hidden="true">↗</span>
                                </>;
                            return href
                                ? <Link href={href} className="discovery-card glass-panel" key={number}>{content}</Link>
                                : <article className="discovery-card glass-panel" key={number}>{content}</article>;
                        })}
                    </div>
                </section>

                <div className="data-ticker" aria-label="Data sources and privacy">
                    <div className="ticker-track">{Array.from({ length: 2 }).map((_, copy) => (
                        <div className="ticker-run" key={copy}>
                            {['Roblox public API', 'No credentials requested', 'Users · inventory · limiteds · bundles', 'Cached · rate limited · public data only', 'Profile checker'].map((text) => <span key={text}>{text}<i /></span>)}
                        </div>
                    ))}</div>
                </div>

                <footer className="home-footer">
                    <div className="home-footer-meta"><span>Public API</span><span>Cached · rate limited</span><span>Privacy first</span></div>
                    <nav aria-label="More tools">
                        <Link href="/compare">Compare</Link><Link href="/watchlist">Watchlist</Link>
                        <Link href="/history">Recent searches</Link><Link href="/api-docs">API docs</Link>
                    </nav>
                </footer>
            </div>
        </AppLayout>
    );
}

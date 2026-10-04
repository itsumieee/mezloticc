import { useMemo, useState } from 'react';
import { Link } from '@inertiajs/react';
import { motion, useReducedMotion } from 'framer-motion';
import AppLayout from '../Layouts/AppLayout';

const games = [
    {
        id: 'mlbb',
        name: 'Mobile Legends',
        shortName: 'MLBB',
        category: 'MOBA / ACCOUNT INDEX',
        description: 'Lihat pratinjau kartu akun berdasarkan Player ID dan Server ID.',
        href: '/mlbb',
        available: true,
        artwork: '/images/games/mobile-legends-hero.jpe',
    },
    {
        id: 'roblox',
        name: 'Roblox',
        shortName: 'ROBLOX',
        category: 'AVATAR / INVENTORY',
        description: 'Periksa profil publik, avatar, inventory, limited, dan bundle.',
        href: '/roblox',
        available: true,
        artwork: '/images/games/roblox-avatar-hero.jpe',
    },
    {
        id: 'free-fire',
        name: 'Free Fire',
        shortName: 'FREE FIRE',
        category: 'BATTLE ROYALE',
        available: false,
        artwork: '/images/games/free-fire.svg',
    },
    {
        id: 'pubg-mobile',
        name: 'PUBG Mobile',
        shortName: 'PUBG',
        category: 'BATTLE ROYALE',
        available: false,
        artwork: '/images/games/pubg-mobile.svg',
    },
    {
        id: 'honor-of-kings',
        name: 'Honor of Kings',
        shortName: 'HOK',
        category: 'MOBA',
        available: false,
        artwork: '/images/games/honor-of-kings.svg',
    },
];

const filters = [
    ['all', 'Semua game'],
    ['available', 'Sudah tersedia'],
    ['upcoming', 'Segera hadir'],
];

export default function GameHub() {
    const [filter, setFilter] = useState('all');
    const reduceMotion = useReducedMotion();
    const visibleGames = useMemo(() => games.filter((game) => {
        if (filter === 'available') return game.available;
        if (filter === 'upcoming') return !game.available;
        return true;
    }), [filter]);

    return (
        <AppLayout title="Pilih game">
            <div className="gamehub-page">
                <div className="gamehub-topline">
                    <span>PROFILE / INDEX</span><i /><span>PUBLIC GAME PROFILES</span>
                </div>

                <header className="gamehub-heading">
                    <div>
                        <p className="eyebrow"><span className="status-dot" />MULTI-GAME ACCOUNT INDEX</p>
                        <h1>Pilih <span className="display-serif">game.</span></h1>
                        <p className="gamehub-description">Satu tempat untuk membuka alat pemeriksaan akun dari game pilihanmu.</p>
                    </div>
                    <span className="gamehub-count"><b>{games.filter((game) => game.available).length}</b> GAME<br />PAGES</span>
                </header>

                <div className="gamehub-toolbar">
                    <div className="gamehub-filters" role="group" aria-label="Filter game">
                        {filters.map(([value, label]) => (
                            <button
                                type="button"
                                key={value}
                                className={filter === value ? 'is-active' : ''}
                                aria-pressed={filter === value}
                                onClick={() => setFilter(value)}
                            >
                                {label}
                            </button>
                        ))}
                    </div>
                    <span className="gamehub-total">{visibleGames.length} JUDUL</span>
                </div>

                <section className="gamehub-grid" aria-label="Pilih game">
                    {visibleGames.map((game, index) => {
                        const Tile = game.available ? Link : 'article';
                        const motionProps = reduceMotion
                            ? {}
                            : {
                                initial: { opacity: 0, y: 14, scale: 0.96 },
                                whileInView: { opacity: 1, y: 0, scale: 1 },
                                viewport: { once: true, amount: 0.15 },
                                transition: { delay: index * 0.055, type: 'spring', stiffness: 210, damping: 18, mass: 0.75 },
                            };

                        return (
                            <motion.div className="gamehub-item" key={game.id} {...motionProps}>
                                <Tile
                                    {...(game.available ? { href: game.href } : { 'aria-disabled': 'true' })}
                                    className={`game-tile glass-panel ${game.available ? 'is-available' : 'is-upcoming'}`}
                                >
                                    <div className={`game-tile-art game-art-${game.id}`} aria-hidden="true">
                                        <img src={game.artwork} alt="" loading="lazy" />
                                        <i />
                                        <b>{game.shortName}</b>
                                    </div>
                                    <div className="game-tile-copy">
                                        <div className="game-tile-meta">
                                            <span>{game.category}</span>
                                            <span className={game.available ? 'game-live' : 'game-soon'}>
                                                {game.available ? 'OPEN ↗' : 'SEGERA HADIR'}
                                            </span>
                                        </div>
                                        <h2>{game.name}</h2>
                                        <p>{game.description || 'Halaman pemeriksaan akun sedang disiapkan.'}</p>
                                    </div>
                                </Tile>
                            </motion.div>
                        );
                    })}
                </section>

                <footer className="gamehub-footer">
                    <span><i className="status-dot" /> DATA SESUAI FITUR YANG SUDAH TERSEDIA</span>
                    <span>GAME ART / USER-SUPPLIED & ORIGINAL</span>
                </footer>
            </div>
        </AppLayout>
    );
}

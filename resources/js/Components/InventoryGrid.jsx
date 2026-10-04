import { useEffect, useMemo, useState } from 'react';
import { Link } from '@inertiajs/react';
import { AnimatePresence, motion } from 'framer-motion';
import { EmptyState } from './GlassPanel';

const sortOptions = [
    ['default', 'Default'], ['rap_desc', 'RAP · High → Low'], ['rap_asc', 'RAP · Low → High'],
    ['name_asc', 'Name · A → Z'], ['name_desc', 'Name · Z → A'], ['id_asc', 'Asset ID · Asc'], ['id_desc', 'Asset ID · Desc'],
];

export function ExportLinks({ userId, category = 'collectibles' }) {
    const query = category ? `?category=${encodeURIComponent(category)}` : '';
    return (
        <details className="export-menu">
            <summary>Export</summary>
            <div><a href={`/dashboard/${userId}/export/csv${query}`}>Download CSV</a><a href={`/dashboard/${userId}/export/json${query}`}>Download JSON</a></div>
        </details>
    );
}

export function Pagination({ previousUrl, nextUrl }) {
    if (!previousUrl && !nextUrl) return null;
    return (
        <nav className="pagination" aria-label="Inventory pages">
            {previousUrl ? <Link href={previousUrl}>← Previous</Link> : <span>← Previous</span>}
            <span>Page</span>
            {nextUrl ? <Link href={nextUrl}>Next →</Link> : <span>Next →</span>}
        </nav>
    );
}

export default function InventoryGrid({ items = [], thumbnails = {}, label = 'Inventory', emptyMessage = 'No items found.', filterable = false }) {
    const [query, setQuery] = useState('');
    const [sort, setSort] = useState('default');
    const [selected, setSelected] = useState(null);
    const visibleItems = useMemo(() => {
        const filtered = items.filter((item) => {
            const name = String(item.name ?? '').toLowerCase();
            const id = String(item.assetId ?? item.id ?? '');
            return !query || name.includes(query.toLowerCase()) || id.includes(query);
        });
        const compare = (a, b) => {
            const nameA = String(a.name ?? '').toLowerCase();
            const nameB = String(b.name ?? '').toLowerCase();
            const idA = Number(a.assetId ?? a.id ?? 0);
            const idB = Number(b.assetId ?? b.id ?? 0);
            const rapA = a.recentAveragePrice == null ? -1 : Number(a.recentAveragePrice);
            const rapB = b.recentAveragePrice == null ? -1 : Number(b.recentAveragePrice);

            switch (sort) {
                case 'rap_desc': return rapB - rapA;
                case 'rap_asc': return (rapA < 0 ? Infinity : rapA) - (rapB < 0 ? Infinity : rapB);
                case 'name_asc': return nameA.localeCompare(nameB);
                case 'name_desc': return nameB.localeCompare(nameA);
                case 'id_asc': return idA - idB;
                case 'id_desc': return idB - idA;
                default: return 0;
            }
        };
        return sort === 'default' ? filtered : [...filtered].sort(compare);
    }, [items, query, sort]);

    return (
        <>
            {filterable && items.length > 0 && (
                <div className="inventory-controls glass-panel">
                    <label>Filter <input value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Name or asset ID" /></label>
                    <label>Sort <select value={sort} onChange={(event) => setSort(event.target.value)}>{sortOptions.map(([value, text]) => <option value={value} key={value}>{text}</option>)}</select></label>
                    <span>{visibleItems.length} shown</span>
                </div>
            )}
            {visibleItems.length === 0 ? <EmptyState label={label}>{emptyMessage}</EmptyState> : (
                <div className="inventory-grid">
                    {visibleItems.map((item, index) => {
                        const assetId = item.assetId ?? item.id;
                        const image = thumbnails[assetId] ?? null;
                        return (
                            <motion.button
                                type="button"
                                key={`${assetId ?? item.name}-${index}`}
                                className="asset-card"
                                onClick={() => setSelected({ ...item, assetId, image })}
                                whileHover={{ y: -5, rotateX: -1 }}
                                transition={{ type: 'spring', stiffness: 250, damping: 22 }}
                            >
                                <span className="asset-index">{String(index + 1).padStart(3, '0')}</span>
                                {item.recentAveragePrice != null && <span className="asset-rap">{Number(item.recentAveragePrice).toLocaleString()} R</span>}
                                <span className="asset-image">
                                    {image ? <img src={image} loading="lazy" alt="" /> : <span>No image</span>}
                                </span>
                                <span className="asset-description"><strong>{item.name ?? 'Unknown'}</strong><small>#{assetId ?? '—'}</small></span>
                                {item.serialNumber && <span className="asset-serial">SN {item.serialNumber}</span>}
                            </motion.button>
                        );
                    })}
                </div>
            )}
            <AnimatePresence>
                {selected && <AssetModal item={selected} close={() => setSelected(null)} />}
            </AnimatePresence>
        </>
    );
}

function AssetModal({ item, close }) {
    const category = item.assetType ?? item.assetTypeId ?? '—';
    const rap = item.recentAveragePrice;

    useEffect(() => {
        const onKeyDown = (event) => {
            if (event.key === 'Escape') close();
        };
        window.addEventListener('keydown', onKeyDown);
        return () => window.removeEventListener('keydown', onKeyDown);
    }, [close]);

    return (
        <motion.div className="modal-backdrop" role="presentation" onMouseDown={(event) => event.target === event.currentTarget && close()} initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}>
            <motion.section className="item-modal glass-panel" role="dialog" aria-modal="true" aria-labelledby="item-title" initial={{ y: 18, scale: 0.98 }} animate={{ y: 0, scale: 1 }} exit={{ y: 12, scale: 0.98 }}>
                <header><div><span className="eyebrow">Inventory item</span><h2 id="item-title">{item.name ?? 'Unknown'}</h2></div><button onClick={close} aria-label="Close">×</button></header>
                <div className="modal-image">{item.image && <img src={item.image} alt="" />}</div>
                <dl className="modal-details">
                    <div><dt>Asset ID</dt><dd>#{item.assetId ?? '—'}</dd></div>
                    <div><dt>Category</dt><dd>{category}</dd></div>
                    <div><dt>RAP</dt><dd>{rap == null ? '—' : `${Number(rap).toLocaleString()} R`}</dd></div>
                    <div><dt>Serial</dt><dd>{item.serialNumber ? `#${item.serialNumber}` : '—'}</dd></div>
                </dl>
                {/^\d+$/.test(String(item.assetId ?? '')) && <a className="button-primary" href={`https://www.roblox.com/catalog/${item.assetId}`} target="_blank" rel="noreferrer">View on Roblox ↗</a>}
            </motion.section>
        </motion.div>
    );
}

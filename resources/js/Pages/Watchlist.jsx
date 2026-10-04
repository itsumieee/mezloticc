import { router, useForm, usePage } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import { EmptyState, GlassPanel, Notice, PageTitle } from '../Components/GlassPanel';
import { formatDateTime } from '../formatters';

export default function Watchlist() {
    const { props } = usePage();
    const form = useForm({ user_id: '', note: '' });
    const paginator = props.items ?? {};
    const items = paginator.data ?? [];
    const presence = props.presence ?? {};
    const alerts = props.alerts ?? {};

    function submit(event) {
        event.preventDefault();
        form.post('/watchlist', { onSuccess: () => form.reset() });
    }

    return (
        <AppLayout title="Watchlist">
            <PageTitle index="WL" title="Watchlist" meta={`${paginator.total ?? 0} tracked accounts`} />
            <Notice>{props.flash?.error}</Notice>
            {props.flash?.status && <p className="status-message" role="status">{props.flash.status}</p>}
            <form className="watchlist-form glass-panel" onSubmit={submit}>
                <label>User ID<input type="number" min="1" value={form.data.user_id} onChange={(event) => form.setData('user_id', event.target.value)} placeholder="Roblox user ID" required /></label>
                <label>Note (optional)<input maxLength="200" value={form.data.note} onChange={(event) => form.setData('note', event.target.value)} placeholder="Add a note" /></label>
                <button className="button-primary" disabled={form.processing}>{form.processing ? 'Adding…' : 'Add account'}</button>
                {(form.errors.user_id || form.errors.note) && <p className="field-error">{form.errors.user_id ?? form.errors.note}</p>}
            </form>
            {items.length === 0 ? <EmptyState label="WL / Empty">No accounts tracked yet.</EmptyState> : (
                <GlassPanel className="watchlist-list">
                    {items.map((item) => {
                        const latest = presence[item.roblox_user_id];
                        const changed = alerts[item.roblox_user_id];
                        return (
                            <article className="watchlist-row" key={item.id ?? item.roblox_user_id}>
                                <div className="watchlist-identity"><h2>{item.display_name || item.username}</h2><p>@{item.username} · ID {item.roblox_user_id}{item.note ? ` · ${item.note}` : ''}</p>
                                    {latest ? <div className="presence-line"><span className="status-dot" />{latest.status_key === 'in-game' ? 'In game' : latest.status_key}{latest.game_name && ` · ${latest.game_name}`} · {formatDateTime(latest.observed_at)}</div> : <small className="muted">Waiting for first presence check</small>}
                                    {changed && <span className="change-label">Status changed recently</span>}
                                </div>
                                <div className="watchlist-actions"><a href={`/dashboard/${item.roblox_user_id}`}>View account</a><button onClick={() => router.delete(`/watchlist/${item.roblox_user_id}`)}>Remove</button></div>
                            </article>
                        );
                    })}
                </GlassPanel>
            )}
            {paginator.links?.length > 3 && <nav className="pagination" aria-label="Watchlist pages">{paginator.links.map((link, index) => {
                const label = link.label.replace('&laquo;', '←').replace('&raquo;', '→');
                return link.url ? <a key={index} href={link.url}>{label}</a> : <span key={index}>{label}</span>;
            })}</nav>}
        </AppLayout>
    );
}

import { Link, usePage } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import { EmptyState, GlassPanel, PageTitle } from '../Components/GlassPanel';
import { formatDateTime } from '../formatters';

export default function History() {
    const { props } = usePage();
    const history = props.history ?? [];

    return (
        <AppLayout title="Search history">
            <PageTitle index="—" title="Search History" meta={`${history.length} recent`} />
            {history.length === 0 ? <EmptyState label="Empty">No searches yet.</EmptyState> : (
                <GlassPanel className="history-list">
                    {history.map((item, index) => (
                        <div className="history-row" key={item.id ?? `${item.query}-${index}`}>
                            <span className="list-index">{String(index + 1).padStart(2, '0')}</span>
                            <div><strong>{item.query}</strong><small>{formatDateTime(item.created_at, ' · ')}</small></div>
                            {item.resolved_user_id && <Link href={`/dashboard/${item.resolved_user_id}`}>View →</Link>}
                        </div>
                    ))}
                </GlassPanel>
            )}
            <Link className="text-link back-link" href="/roblox">← Back to Roblox lookup</Link>
        </AppLayout>
    );
}

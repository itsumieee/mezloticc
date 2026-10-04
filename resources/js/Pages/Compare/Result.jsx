import { Link, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { GlassPanel, PageTitle } from '../../Components/GlassPanel';
import { formatDateTime } from '../../formatters';

export default function CompareResult() {
    const { props } = usePage();
    const { userA, userB, dataA, dataB } = props;
    const rap = (data) => data?.rap ?? {};
    const format = (value) => typeof value === 'number' ? value.toLocaleString() : value ?? '—';
    const rows = [
        ['Username', userA.name, userB.name],
        ['Display name', userA.displayName, userB.displayName],
        ['User ID', userA.id, userB.id],
        ['Created', formatDateTime(dataA.created, ''), formatDateTime(dataB.created, '')],
        ['Limited items', dataA.limited, dataB.limited],
        ['Total RAP', rap(dataA).total_rap, rap(dataB).total_rap],
        ['Average RAP', rap(dataA).average_rap, rap(dataB).average_rap],
        ['Wearing', dataA.wearing, dataB.wearing],
        ['Bundles', dataA.bundles, dataB.bundles],
    ];

    return (
        <AppLayout title="Comparison result">
            <PageTitle index="CMP" title="Comparison" meta="Public data only" actions={<Link className="button-quiet" href="/compare">Compare again</Link>} />
            <GlassPanel className="comparison-table">
                <div className="comparison-header"><div className="comparison-label">Metric</div>{[[userA, dataA], [userB, dataB]].map(([user, data]) => <div className="comparison-account" key={user.id}>{data.headshot && <img src={data.headshot} alt="" />}<strong>{user.displayName ?? user.name ?? 'Roblox user'}</strong></div>)}</div>
                {rows.map(([label, a, b]) => <div className="comparison-row" key={label}><strong>{label}</strong><span>{format(a)}</span><span>{format(b)}</span></div>)}
            </GlassPanel>
        </AppLayout>
    );
}

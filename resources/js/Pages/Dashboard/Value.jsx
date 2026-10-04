import { usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { EmptyState, GlassPanel, Notice, PageTitle } from '../../Components/GlassPanel';
import DataChart from '../../Components/DataCharts';
import { RefreshButton } from '../../Components/ProfileCard';

export default function Value() {
    const { props } = usePage();
    const { userId, rapData = {}, rapValues = [], error, thirdParty } = props;
    const hasData = Number(rapData.item_count ?? 0) > 0;
    const buckets = [
        ['0–99', 0, 100], ['100–499', 100, 500], ['500–1K', 500, 1000],
        ['1K–5K', 1000, 5000], ['5K–10K', 5000, 10000], ['10K–50K', 10000, 50000],
        ['50K–100K', 50000, 100000], ['100K+', 100000, Infinity],
    ];
    const distribution = buckets.map(([, min, max]) => rapValues.filter((value) => value >= min && value < max).length);

    return (
        <AppLayout title="Value">
            <PageTitle index="09" title="Value" meta="Recent average price · Public data" actions={<RefreshButton userId={userId} />} />
            {error && <Notice> {error} RAP totals require a complete Limited-items scan.</Notice>}
            {hasData ? <>
                <div className="metrics-grid value-grid">
                    <ValueMetric label="Total RAP" value={rapData.total_rap} accent />
                    <ValueMetric label="Average" value={rapData.average_rap} />
                    <ValueMetric label="Items w/ RAP" value={rapData.item_count} />
                </div>
                <div className="value-extremes">
                    {rapData.highest && <ValueMetric label="↑ Highest" value={rapData.highest.rap} name={rapData.highest.name} accent />}
                    {rapData.lowest && <ValueMetric label="↓ Lowest" value={rapData.lowest.rap} name={rapData.lowest.name} />}
                </div>
                <GlassPanel className="chart-panel"><div className="section-heading"><span>RAP</span><h2>RAP Distribution</h2><i /><span>BUCKETS</span></div><DataChart labels={buckets.map(([label]) => label)} values={distribution} /></GlassPanel>
            </> : <EmptyState label="Data unavailable">RAP data is not available for this account. The inventory may be private, or the items do not have public resale data.</EmptyState>}
            {thirdParty && <GlassPanel className="third-party"><div className="section-heading"><span>THIRD PARTY</span><h2>Rolimons · Unofficial</h2><i /></div><pre>{JSON.stringify(thirdParty, null, 2)}</pre><p>Rolimons is not affiliated with Roblox. Third-party data may differ from Roblox RAP and is not guaranteed to be accurate.</p></GlassPanel>}
        </AppLayout>
    );
}

function ValueMetric({ label, value, name, accent = false }) {
    return <GlassPanel className="value-metric"><span className={`eyebrow ${accent ? 'accent' : ''}`}>{label}</span>{name && <h3>{name}</h3>}<strong className={accent ? 'accent' : ''}>{Number(value ?? 0).toLocaleString()}</strong></GlassPanel>;
}

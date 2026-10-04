import { useState } from 'react';
import AppLayout from '../Layouts/AppLayout';
import { GlassPanel, Notice, PageTitle } from '../Components/GlassPanel';

const emptyMetrics = [
    ['Account rating', 'Not available', 'Requires verified account data'],
    ['Total skins', 'Not available', 'No collection has been read'],
    ['Top-up estimate', 'Not available', 'No verified purchase history'],
    ['Collector tier', 'Not available', 'Requires collection data'],
];

const skinGroups = [
    ['Highest value', 'Most expensive skins'],
    ['Collaboration', 'Collab skins'],
    ['Limited', 'Limited skins'],
];

function downloadFile(filename, content, type) {
    const url = URL.createObjectURL(new Blob([content], { type }));
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.click();
    window.setTimeout(() => URL.revokeObjectURL(url), 1000);
}

export default function Mlbb() {
    const [playerId, setPlayerId] = useState('');
    const [serverId, setServerId] = useState('');
    const [submitted, setSubmitted] = useState(false);
    const [error, setError] = useState('');
    const canExport = /^\d{1,15}$/.test(playerId) && /^\d{1,6}$/.test(serverId);

    const submitLookup = (event) => {
        event.preventDefault();
        if (!canExport) {
            setError('Masukkan Player ID dan Server ID berupa angka.');
            setSubmitted(false);
            return;
        }

        setError('');
        setSubmitted(true);
    };

    const downloadReport = () => {
        const report = {
            game: 'Mobile Legends: Bang Bang',
            playerId,
            serverId,
            dataStatus: 'unavailable',
            note: 'No MLBB data API is configured. Account details, ratings, skins, and estimates were not fetched.',
            accountRating: null,
            totalSkins: null,
            topUpEstimate: null,
            collectorTier: null,
            skins: {
                highestValue: null,
                collaborations: null,
                limited: null,
            },
        };

        downloadFile(`mlbb-report-${playerId}-${serverId}-unverified.json`, JSON.stringify(report, null, 2), 'application/json');
    };

    const downloadCard = () => {
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630">
<defs><linearGradient id="chrome" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#314852"/><stop offset=".22" stop-color="#5d7179"/><stop offset=".48" stop-color="#243943"/><stop offset=".7" stop-color="#5d7179"/><stop offset="1" stop-color="#314852"/></linearGradient></defs>
<rect width="1200" height="630" fill="#edf3f6"/>
<path d="M40 40H1160V590H40z" fill="#f9fbfc" stroke="#8ba8b4" stroke-opacity=".7"/>
<text x="80" y="105" fill="#4f626e" font-family="Space Mono,monospace" font-size="18" letter-spacing="5">PROFILE / INDEX · MLBB</text>
<text x="80" y="220" fill="url(#chrome)" stroke="#526a74" stroke-opacity=".2" paint-order="stroke" font-family="Syne,sans-serif" font-size="62" font-weight="800">ACCOUNT REPORT</text>
<text x="80" y="300" fill="#4f626e" font-family="DM Sans,sans-serif" font-size="22">PLAYER ID</text>
<text x="80" y="345" fill="#172630" font-family="Space Mono,monospace" font-size="32">${playerId}</text>
<text x="430" y="300" fill="#4f626e" font-family="DM Sans,sans-serif" font-size="22">SERVER ID</text>
<text x="430" y="345" fill="#172630" font-family="Space Mono,monospace" font-size="32">${serverId}</text>
<text x="80" y="455" fill="#005c6b" font-family="DM Sans,sans-serif" font-size="24">DATA NOT AVAILABLE · API NOT CONNECTED</text>
<text x="80" y="520" fill="#4f626e" font-family="DM Sans,sans-serif" font-size="18">No account rating, skin inventory, or spending estimate has been verified.</text>
</svg>`;

        downloadFile(`mlbb-card-${playerId}-${serverId}-unverified.svg`, svg, 'image/svg+xml');
    };

    return (
        <AppLayout title="Mobile Legends account preview">
            <div className="mlbb-page">
                <PageTitle
                    index="MLBB"
                    title={<>Mobile<br /><span className="display-serif">Legends</span></>}
                    meta="ACCOUNT INDEX / DATA PREVIEW"
                    actions={<span className="mlbb-api-status"><i />API NOT CONNECTED</span>}
                />

                <GlassPanel className="mlbb-lookup">
                    <div className="mlbb-lookup-copy">
                        <p className="eyebrow">PLAYER LOOKUP / ID + SERVER</p>
                        <h2>Account report</h2>
                        <p>Masukkan ID publik untuk menyiapkan kartu laporan. Data akun belum dapat diperiksa sampai sumber API MLBB dikonfigurasi.</p>
                    </div>
                    <form className="mlbb-lookup-form" onSubmit={submitLookup}>
                        <label>
                            Player ID
                            <input
                                inputMode="numeric"
                                autoComplete="off"
                                maxLength={15}
                                placeholder="Contoh: 123456789"
                                value={playerId}
                                onChange={(event) => setPlayerId(event.target.value.replace(/\D/g, '').slice(0, 15))}
                            />
                        </label>
                        <label>
                            Server ID
                            <input
                                inputMode="numeric"
                                autoComplete="off"
                                maxLength={6}
                                placeholder="Contoh: 1234"
                                value={serverId}
                                onChange={(event) => setServerId(event.target.value.replace(/\D/g, '').slice(0, 6))}
                            />
                        </label>
                        <button className="button-primary" type="submit">Prepare account card <span aria-hidden="true">↗</span></button>
                        {error && <p className="field-error" role="alert">{error}</p>}
                        {submitted && <p className="mlbb-lookup-note" role="status">ID tersimpan hanya di tampilan ini. Pencarian akun belum dilakukan karena API belum tersedia.</p>}
                    </form>
                </GlassPanel>

                <Notice kind="warning">
                    API MLBB belum terhubung. Nama akun, rank, koleksi skin, nilai, dan estimasi top-up tidak akan ditampilkan sebagai fakta sampai data sumber tersedia.
                </Notice>

                <section className="mlbb-metrics" aria-label="Account analysis preview">
                    {emptyMetrics.map(([label, value, detail]) => (
                        <GlassPanel className="mlbb-metric" key={label}>
                            <span className="eyebrow">{label}</span>
                            <strong>{value}</strong>
                            <small>{detail}</small>
                        </GlassPanel>
                    ))}
                </section>

                <section className="mlbb-profile-stage" aria-label="Share card preview">
                    <GlassPanel className="mlbb-share-card">
                        <div className="mlbb-share-card-art" aria-hidden="true"><span>MLBB</span><i /></div>
                        <div className="mlbb-share-card-copy">
                            <p className="eyebrow">PROFILE CARD / UNVERIFIED</p>
                            <h2>{submitted ? `Player ${playerId}` : 'Account preview'}</h2>
                            <p>Server {submitted ? serverId : '—'} <span>·</span> Account data unavailable</p>
                            <div className="mlbb-card-facts">
                                <span>BEST SKIN <b>Not available</b></span>
                                <span>COLLECTION <b>Not available</b></span>
                                <span>RATING <b>Not available</b></span>
                            </div>
                            <div className="mlbb-export-actions">
                                <button type="button" className="button-quiet" onClick={downloadCard} disabled={!canExport}>Download card · SVG</button>
                                <button type="button" className="button-quiet" onClick={downloadReport} disabled={!canExport}>Download report · JSON</button>
                            </div>
                            {!canExport && <small className="mlbb-export-hint">Isi Player ID dan Server ID untuk mengunduh kartu bertanda belum terverifikasi.</small>}
                        </div>
                    </GlassPanel>
                </section>

                <section className="mlbb-skins" aria-labelledby="mlbb-skins-title">
                    <div className="section-heading">
                        <span>COLLECTION</span><h2 id="mlbb-skins-title">Skin index</h2><i /><span>AWAITING DATA</span>
                    </div>
                    <div className="mlbb-skin-groups">
                        {skinGroups.map(([key, label], index) => (
                            <GlassPanel className="mlbb-skin-group" key={key}>
                                <span className="eyebrow">0{index + 1} / {key}</span>
                                <strong>{label}</strong>
                                <p>Daftar skin akan muncul setelah API koleksi tersedia.</p>
                                <span className="mlbb-unavailable">NO VERIFIED ITEMS</span>
                            </GlassPanel>
                        ))}
                    </div>
                </section>

                <GlassPanel className="mlbb-value-note">
                    <div>
                        <p className="eyebrow">VALUATION / TRANSPARENCY</p>
                        <h2>Estimasi bukan bukti transaksi.</h2>
                    </div>
                    <p>Jika data MLBB tersedia nanti, nilai top-up akan ditandai sebagai estimasi dan dipisahkan dari riwayat pembelian resmi. Tidak ada harga atau total koleksi yang direka di halaman ini.</p>
                </GlassPanel>
            </div>
        </AppLayout>
    );
}

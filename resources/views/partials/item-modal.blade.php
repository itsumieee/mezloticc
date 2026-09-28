<div id="itemModal"
     class="item-modal-backdrop fixed inset-0 z-50 hidden items-center justify-center"
     role="dialog" aria-modal="true" aria-labelledby="modalTitle" tabindex="-1"
     onclick="if(event.target===this) closeItemModal()">

    <div class="item-modal-dialog">

        <div class="item-modal-header">
            <div>
                <div class="item-modal-eyebrow">Inventory item</div>
                <h3 id="modalTitle" class="item-modal-title">—</h3>
            </div>
            <button type="button" onclick="closeItemModal()" class="item-modal-close" aria-label="Close item details">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" /></svg>
            </button>
        </div>

        <div class="item-modal-image">
            <img id="modalImage" src="" alt="" class="w-56 h-56 object-contain">
        </div>

        <div class="item-modal-details">
            <div class="item-modal-row">
                <span>Asset ID</span>
                <span id="modalAssetId">—</span>
            </div>
            <div class="item-modal-row">
                <span>Category</span>
                <span id="modalCategory">—</span>
            </div>
            <div class="item-modal-row">
                <span>RAP</span>
                <span id="modalRap" class="item-modal-rap">—</span>
            </div>
            <div class="item-modal-row">
                <span>Serial</span>
                <span id="modalSerial">—</span>
            </div>
        </div>

        <a id="modalLink" href="#" target="_blank"
           class="item-modal-action" rel="noopener noreferrer">
            View on Roblox <span aria-hidden="true">↗</span>
        </a>
    </div>
</div>

<script>
function openItemModal(data) {
    document.getElementById('modalTitle').textContent = data.name || 'Unknown';
    document.getElementById('modalImage').src = data.image || '';
    document.getElementById('modalAssetId').textContent = data.assetId ? '#' + data.assetId : '—';
    document.getElementById('modalCategory').textContent = data.category || '—';
    document.getElementById('modalRap').textContent = data.rap !== null && data.rap !== undefined
        ? Number(data.rap).toLocaleString() + ' R'
        : '—';
    document.getElementById('modalSerial').textContent = data.serial ? '#' + data.serial : '—';
    document.getElementById('modalLink').href = /^\d+$/.test(String(data.assetId || ''))
        ? 'https://www.roblox.com/catalog/' + data.assetId
        : '#';

    const m = document.getElementById('itemModal');
    m.classList.remove('hidden');
    m.classList.add('flex');
    document.body.style.overflow = 'hidden';
    m.querySelector('.item-modal-close')?.focus();
}

function closeItemModal() {
    const m = document.getElementById('itemModal');
    m.classList.add('hidden');
    m.classList.remove('flex');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeItemModal(); });
</script>
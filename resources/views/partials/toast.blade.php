<div id="toastStack" class="fixed bottom-6 right-6 z-[120] flex flex-col gap-3 pointer-events-none"></div>

<script>
window.toast = function (message, kind = 'info', ttl = 3200) {
    const stack = document.getElementById('toastStack');
    if (!stack) return;

    const toneMap = {
        info: ['border-acid', 'text-acid', '●'],
        success: ['border-acid', 'text-acid', '✓'],
        error: ['border-signal', 'text-signal', '✕'],
    };

    const tone = toneMap[kind] || toneMap.info;
    const el = document.createElement('div');

    el.className = `pointer-events-auto border ${tone[0]} bg-ink/95 backdrop-blur px-5 py-4 font-mono text-[11px] tracking-[0.15em] uppercase text-paper translate-x-[120%] transition-transform duration-300 ease-out flex items-center gap-3 min-w-[260px]`;
    el.innerHTML = `<span class="${tone[1]} text-base">${tone[2]}</span><span>${message}</span>`;
    stack.appendChild(el);

    requestAnimationFrame(() => {
        el.style.transform = 'translateX(0)';
    });

    setTimeout(() => {
        el.style.transform = 'translateX(120%)';
        setTimeout(() => el.remove(), 320);
    }, ttl);
};

@if (session('status'))
    window.addEventListener('DOMContentLoaded', () => window.toast(@json(session('status')), 'success'));
@endif

@if (session('error'))
    window.addEventListener('DOMContentLoaded', () => window.toast(@json(session('error')), 'error', 4500));
@endif
</script>

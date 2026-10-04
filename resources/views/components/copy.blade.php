@props(['value' => '', 'label' => null])

<button type="button"
        onclick="copyToClipboard(@json((string) $value), this)"
        class="group inline-flex items-center gap-2 font-mono text-[10px] tracking-[0.2em] uppercase text-paper/50 hover:text-acid transition-colors">
    @if($label)
        <span class="text-paper/30">{{ $label }}</span>
    @endif
    <span class="tabular-nums">{{ $value }}</span>
    <span class="copy-icon opacity-0 group-hover:opacity-100 transition-opacity">⎘</span>
</button>

@once
@push('scripts')
<script>
window.copyToClipboard = async function (text, el) {
    try {
        await navigator.clipboard.writeText(String(text));
        const icon = el.querySelector('.copy-icon');
        if (icon) {
            icon.textContent = '✓';
            icon.classList.remove('opacity-0');
            setTimeout(() => {
                icon.textContent = '⎘';
                icon.classList.add('opacity-0');
            }, 1400);
        }
        window.toast && window.toast('Copied to clipboard', 'success', 1800);
    } catch (error) {
        window.toast && window.toast('Copy failed', 'error');
    }
};
</script>
@endpush
@endonce

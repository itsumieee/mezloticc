<div id="shortcutsPanel" class="shortcuts-panel" onclick="if (event.target === this) closeShortcuts()">
    <div class="shortcuts-card">
        <div class="shortcuts-header">
            <div class="shortcuts-tag">Shortcuts</div>
            <button type="button" onclick="closeShortcuts()" aria-label="Close shortcuts" class="shortcuts-close">&times;</button>
        </div>

        <div class="shortcuts-title-row">
            <h3>Keyboard</h3>
        </div>

        <div class="shortcuts-list">
            @foreach([
                ['⌘ K', 'Open command palette'],
                ['/', 'Focus search input'],
                ['ESC', 'Close modal / palette'],
                ['G H', 'Go home'],
                ['G I', 'Go inventory (dashboard)'],
                ['G L', 'Go limited (dashboard)'],
                ['?', 'Toggle this panel'],
            ] as [$key, $desc])
                <div class="shortcut-row">
                    <span class="shortcut-label">{{ $desc }}</span>
                    <span class="shortcut-key">{{ $key }}</span>
                </div>
            @endforeach
        </div>

        <div class="shortcuts-footer">Press ? anytime</div>
    </div>
</div>

<script>
window.openShortcuts = () => {
    const panel = document.getElementById('shortcutsPanel');
    if (!panel) return;
    panel.classList.remove('hidden');
    panel.classList.add('is-open');
};

window.closeShortcuts = () => {
    const panel = document.getElementById('shortcutsPanel');
    if (!panel) return;
    panel.classList.add('hidden');
    panel.classList.remove('is-open');
};

(function () {
    const hasDashboard = {{ request()->routeIs('dashboard.*') ? 'true' : 'false' }};
    const uid = {{ (int) (request()->route('userId') ?? 0) }};
    let gPressed = 0;

    document.addEventListener('keydown', (event) => {
        const activeTag = document.activeElement?.tagName;
        const inField = ['INPUT', 'TEXTAREA', 'SELECT'].includes(activeTag);
        if (inField) return;

        if (event.key === '?') {
            event.preventDefault();
            openShortcuts();
            return;
        }

        if (event.key === '/') {
            const input = document.getElementById('usernameInput') || document.getElementById('itemSearch');
            if (input) {
                event.preventDefault();
                input.focus();
            }
            return;
        }

        if (event.key.toLowerCase() === 'g') {
            gPressed = Date.now();
            return;
        }

        if (Date.now() - gPressed < 800) {
            if (event.key.toLowerCase() === 'h') window.location = '/';
            if (hasDashboard && event.key.toLowerCase() === 'i') window.location = `/dashboard/${uid}/inventory`;
            if (hasDashboard && event.key.toLowerCase() === 'l') window.location = `/dashboard/${uid}/limited`;
            gPressed = 0;
        }
    });
})();
</script>

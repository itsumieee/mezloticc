(() => {
    const palette = document.getElementById('cmdPalette');
    const input = document.getElementById('cmdInput');
    const results = document.getElementById('cmdResults');
    if (!palette || !input || !results) return;

    const dashboard = document.body.dataset.dashboard === 'true';
    const userId = document.body.dataset.userId || '';
    const pages = [
        ['Overview', ''],
        ['Profile', '/profile'],
        ['Inventory', '/inventory'],
        ['Limited', '/limited'],
        ['Animations', '/animations'],
        ['Avatar', '/avatar'],
        ['Bundles', '/bundles'],
        ['Statistics', '/statistics'],
        ['Value', '/value'],
    ];
    let currentItems = [];
    let selectedIndex = 0;
    let returnFocus = null;

    const makeOption = (item, index) => {
        const option = document.createElement('button');
        option.type = 'button';
        option.className = 'command-result';
        option.id = `command-result-${index}`;
        option.setAttribute('role', 'option');
        option.setAttribute('aria-selected', String(index === selectedIndex));

        const label = document.createElement('span');
        label.className = 'command-result-label';
        label.textContent = item.label;
        const hint = document.createElement('span');
        hint.className = 'command-result-hint';
        hint.textContent = item.hint;
        option.append(label, hint);
        option.addEventListener('click', () => selectItem(index));
        return option;
    };

    function render(query = '') {
        const normalized = query.trim().toLowerCase();
        currentItems = [];
        results.replaceChildren();

        const matchingPages = dashboard
            ? pages.filter(([label]) => !normalized || label.toLowerCase().includes(normalized))
                .map(([label, path]) => ({ label, hint: 'GO TO PAGE', url: `/dashboard/${userId}${path}` }))
            : [];
        const actions = [
            { label: 'Home · Account checker', hint: 'HOME', url: '/' },
            { label: 'Recent searches', hint: 'HISTORY', url: '/history' },
            { label: 'Compare accounts', hint: 'COMPARE', url: '/compare' },
            { label: 'Watchlist', hint: 'WATCHLIST', url: '/watchlist' },
            { label: 'Public API documentation', hint: 'API', url: '/api-docs' },
        ].filter(item => !normalized || item.label.toLowerCase().includes(normalized));

        if (matchingPages.length) appendGroup('Dashboard', matchingPages);
        if (actions.length) appendGroup('Quick links', actions);
        if (normalized) {
            appendGroup('Check username', [{
                label: `Search Roblox username “${query.trim()}”`,
                hint: 'SEARCH',
                url: `/?q=${encodeURIComponent(query.trim())}`,
            }]);
        }

        if (!currentItems.length) {
            const empty = document.createElement('p');
            empty.className = 'command-empty';
            empty.textContent = 'No matching pages or actions.';
            results.append(empty);
            return;
        }

        selectedIndex = Math.min(selectedIndex, currentItems.length - 1);
        updateSelection();
    }

    function appendGroup(title, items) {
        const heading = document.createElement('div');
        heading.className = 'command-group-title';
        heading.textContent = title;
        results.append(heading);
        items.forEach(item => {
            const index = currentItems.push(item) - 1;
            results.append(makeOption(item, index));
        });
    }

    function updateSelection() {
        results.querySelectorAll('[role="option"]').forEach((option, index) => {
            const active = index === selectedIndex;
            option.setAttribute('aria-selected', String(active));
            if (active) option.scrollIntoView({ block: 'nearest' });
        });
    }

    function selectItem(index) {
        const item = currentItems[index];
        if (item?.url) window.location.assign(item.url);
        closeCmd();
    }

    function openCmd() {
        returnFocus = document.activeElement;
        palette.classList.add('is-open');
        palette.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        input.value = '';
        selectedIndex = 0;
        render();
        input.focus();
    }

    function closeCmd() {
        palette.classList.remove('is-open');
        palette.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        returnFocus?.focus?.();
    }

    window.openCmdPalette = openCmd;
    window.closeCmdPalette = closeCmd;
    input.addEventListener('input', () => { selectedIndex = 0; render(input.value); });
    palette.addEventListener('click', event => { if (event.target === palette) closeCmd(); });

    document.addEventListener('keydown', event => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            palette.classList.contains('is-open') ? closeCmd() : openCmd();
            return;
        }
        if (!palette.classList.contains('is-open')) return;
        if (event.key === 'Escape') closeCmd();
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, currentItems.length - 1);
            updateSelection();
        }
        if (event.key === 'ArrowUp') {
            event.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, 0);
            updateSelection();
        }
        if (event.key === 'Enter' && currentItems.length) {
            event.preventDefault();
            selectItem(selectedIndex);
        }
    });
})();

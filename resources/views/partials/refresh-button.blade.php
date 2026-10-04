<div class="flex items-center gap-3">
    @if(session('status'))
        <span role="status" class="font-mono text-[10px] tracking-wider text-acid">{{ session('status') }}</span>
    @endif
    <a href="{{ route('home') }}"
       class="border border-white/15 hover:border-acid hover:text-acid px-4 py-2
              font-mono text-[10px] tracking-[0.25em] uppercase transition-colors">
        <span aria-hidden="true">⌂</span> Home / Check
    </a>
    <form method="POST" action="{{ route('dashboard.refresh', $userId) }}" class="inline"
          onsubmit="const button = this.querySelector('button[type=submit]'); if (button) { button.disabled = true; button.textContent = 'Refreshing...'; }">
        @csrf
        <button type="submit"
                class="border border-white/15 hover:border-acid hover:text-acid px-4 py-2
                       font-mono text-[10px] tracking-[0.25em] uppercase transition-colors">
            ⟳ Refresh
        </button>
    </form>
</div>
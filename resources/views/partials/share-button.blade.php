<button type="button" onclick="shareCurrentPage(this)"
        class="min-h-11 rounded-md border border-black/15 bg-white px-4 text-sm font-medium text-paper hover:border-black/30"
        aria-label="Share this Roblox account">
    Share
</button>

@once
    @push('scripts')
        <script>
            window.shareCurrentPage = async (button) => {
                const title = document.title;
                const text = document.querySelector('.profile-display-name')?.textContent?.trim() || title;
                const url = window.location.href;

                try {
                    if (navigator.share) {
                        await navigator.share({ title, text, url });
                    } else if (navigator.clipboard?.writeText) {
                        await navigator.clipboard.writeText(url);
                        window.toast?.('Link copied', 'success');
                    } else {
                        window.prompt('Copy this link', url);
                    }
                } catch (error) {
                    if (error?.name !== 'AbortError') {
                        window.toast?.('Could not share this link', 'error');
                    }
                }
            };
        </script>
    @endpush
@endonce
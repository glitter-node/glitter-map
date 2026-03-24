<div
    x-data="{
        theme: document.documentElement.dataset.theme || 'dark',
        announcement: '',
        icons: {
            dark: 'moon',
            dim: 'half',
            light: 'sun',
        },
        cycle() {
            const next = this.theme === 'dark'
                ? 'dim'
                : (this.theme === 'dim' ? 'light' : 'dark');

            this.theme = next;
            window.setTheme(next);
            this.announcement = `Theme switched to ${next}.`;
        },
    }"
>
    <button
        type="button"
        class="btn-secondary"
        @click="cycle()"
        aria-label="Switch theme"
        :title="`Theme: ${theme}`"
    >
        <span x-show="icons[theme] === 'moon'" aria-hidden="true">Moon</span>
        <span x-show="icons[theme] === 'half'" aria-hidden="true">Dim</span>
        <span x-show="icons[theme] === 'sun'" aria-hidden="true">Sun</span>
    </button>
    <span class="sr-only" aria-live="polite" x-text="announcement"></span>
</div>

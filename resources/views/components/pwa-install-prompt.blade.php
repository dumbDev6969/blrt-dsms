<div
    x-data="pwaInstall()"
    x-show="showPrompt"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    class="fixed bottom-4 left-4 right-4 z-50 md:left-auto md:right-4 md:w-96"
    role="banner"
    aria-label="Install app prompt"
>
    <div class="rounded-xl border border-zinc-700 bg-zinc-900/95 backdrop-blur-md p-4 shadow-2xl">
        <div class="flex items-center gap-3">
            {{-- App icon --}}
            <img
                src="/icons/icon-72x72.png"
                class="size-11 shrink-0 rounded-lg shadow"
                alt="BLRT-DSMS app icon"
                loading="eager"
            >

            {{-- Text --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white leading-tight truncate">
                    Install BLRT-DSMS
                </p>
                <p class="text-xs text-zinc-400 mt-0.5 leading-snug">
                    Add to home screen for quick access
                </p>
            </div>

            {{-- Install button --}}
            <button
                id="pwa-install-btn"
                @click="install()"
                class="shrink-0 rounded-lg bg-blue-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-600 active:scale-95 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-zinc-900"
            >
                Install
            </button>

            {{-- Dismiss --}}
            <button
                id="pwa-dismiss-btn"
                @click="dismiss()"
                class="shrink-0 ml-0.5 rounded-md p-1 text-zinc-500 hover:text-zinc-300 hover:bg-zinc-800 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-zinc-500"
                aria-label="Dismiss install prompt"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</div>

{{--
    Alpine component — registers on alpine:init which fires before Alpine boots.
    This component must be included BEFORE @fluxScripts in the layout so that
    the event listener is in place when Alpine initialises.
--}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('pwaInstall', () => ({
            showPrompt: false,
            deferredPrompt: null,

            init() {
                // Don't show if already installed (running in standalone mode).
                if (window.matchMedia('(display-mode: standalone)').matches) {
                    return;
                }

                // Don't show if the user previously dismissed.
                const dismissed = localStorage.getItem('pwa-install-dismissed');
                if (dismissed) {
                    // Re-show after 30 days.
                    const thirtyDays = 30 * 24 * 60 * 60 * 1000;
                    if (Date.now() - parseInt(dismissed, 10) < thirtyDays) {
                        return;
                    }
                    localStorage.removeItem('pwa-install-dismissed');
                }

                window.addEventListener('beforeinstallprompt', (e) => {
                    // Prevent the mini-infobar from appearing on mobile.
                    e.preventDefault();
                    this.deferredPrompt = e;
                    this.showPrompt = true;
                });

                // Hide if the user installs via the browser's own UI.
                window.addEventListener('appinstalled', () => {
                    this.showPrompt = false;
                    this.deferredPrompt = null;
                });
            },

            async install() {
                if (!this.deferredPrompt) return;

                this.deferredPrompt.prompt();
                const { outcome } = await this.deferredPrompt.userChoice;

                // Clean up regardless of outcome.
                this.deferredPrompt = null;
                this.showPrompt = false;

                if (outcome === 'accepted') {
                    localStorage.removeItem('pwa-install-dismissed');
                }
            },

            dismiss() {
                this.showPrompt = false;
                // Record dismissal timestamp for the 30-day cooldown.
                localStorage.setItem('pwa-install-dismissed', Date.now().toString());
            },
        }));
    });
</script>

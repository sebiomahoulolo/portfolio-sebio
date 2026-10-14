{{--
    Fenêtres de l'administration qui remplacent les boîtes du navigateur :
    - confirmation avant une action (suppression...) ;
    - session expirée (au lieu de l'alerte « This page has expired » de Livewire).

    Utilisation sur un bouton :
        data-confirm="Supprimer ce service ?"
        data-confirm-text="Cette action est définitive."      (facultatif)
        data-confirm-button="Supprimer"                       (facultatif)
        x-on:click="$confirm($el, () => $wire.delete(5))"
--}}
<div
    x-data="{
        open: false,
        expired: false,
        title: '',
        text: '',
        button: 'Confirmer',
        run: null,
        busy: false,
        ask(detail) {
            this.title = detail.title;
            this.text = detail.text || '';
            this.button = detail.button || 'Confirmer';
            this.run = detail.run;
            this.busy = false;
            this.open = true;
        },
        async accept() {
            if (! this.run || this.busy) return;
            this.busy = true;
            try { await this.run(); } finally { this.open = false; this.busy = false; this.run = null; }
        },
    }"
    x-on:confirm-action.window="ask($event.detail)"
    x-on:session-expired.window="expired = true"
    x-on:keydown.escape.window="open = false"
>
    {{-- Confirmation --}}
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-[100] flex items-end justify-center p-4 sm:items-center" role="alertdialog" aria-modal="true" x-bind:aria-label="title">
            <div x-show="open" x-transition.opacity.duration.200ms class="absolute inset-0 bg-zinc-950/50 backdrop-blur-sm" x-on:click="open = false"></div>
            <div x-show="open" x-trap.noscroll="open"
                x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 sm:scale-95"
                class="relative w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-2xl dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex gap-4">
                    <div class="grid size-11 shrink-0 place-items-center rounded-full bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-400">
                        <flux:icon name="exclamation-triangle" class="size-6" />
                    </div>
                    <div class="min-w-0 pt-1">
                        <h2 class="text-base font-semibold text-ink dark:text-paper" x-text="title"></h2>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400" x-show="text" x-text="text"></p>
                    </div>
                </div>
                <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <flux:button variant="ghost" x-on:click="open = false">Annuler</flux:button>
                    <flux:button variant="danger" x-on:click="accept()" x-bind:disabled="busy">
                        <span x-text="busy ? 'Un instant…' : button"></span>
                    </flux:button>
                </div>
            </div>
        </div>
    </template>

    {{-- Session expirée --}}
    <template x-teleport="body">
        <div x-show="expired" x-cloak class="fixed inset-0 z-[110] flex items-end justify-center p-4 sm:items-center" role="alertdialog" aria-modal="true" aria-label="Session expirée">
            <div x-show="expired" x-transition.opacity class="absolute inset-0 bg-zinc-950/60 backdrop-blur-sm"></div>
            <div x-show="expired" x-trap.noscroll="expired"
                x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                class="relative w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-2xl dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex gap-4">
                    <div class="grid size-11 shrink-0 place-items-center rounded-full bg-orange-50 text-copper-dark dark:bg-orange-500/15 dark:text-copper">
                        <flux:icon name="clock" class="size-6" />
                    </div>
                    <div class="min-w-0 pt-1">
                        <h2 class="text-base font-semibold text-ink dark:text-paper">Votre session a expiré</h2>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Par sécurité, vous avez été inactif trop longtemps. Rechargez la page pour continuer ; si besoin, vous serez invité à vous reconnecter.</p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <flux:button variant="primary" icon="arrow-path" x-on:click="window.location.reload()">Recharger la page</flux:button>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        // $confirm(bouton, action) : lit le message sur le bouton et ouvre la fenêtre.
        Alpine.magic('confirm', () => (el, run) => {
            window.dispatchEvent(new CustomEvent('confirm-action', { detail: {
                title: el.dataset.confirm || 'Confirmer cette action ?',
                text: el.dataset.confirmText || '',
                button: el.dataset.confirmButton || 'Confirmer',
                run,
            } }));
        });
    });

    document.addEventListener('livewire:init', () => {
        // Session expirée (erreur 419) : fenêtre propre au lieu de l'alerte du navigateur.
        Livewire.hook('request', ({ fail }) => {
            fail(({ status, preventDefault }) => {
                if (status === 419) {
                    preventDefault();
                    window.dispatchEvent(new CustomEvent('session-expired'));
                }
            });
        });
    });
</script>

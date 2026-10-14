<div class="flex flex-col gap-6">
    <div>
        <flux:heading size="lg">Vérification en deux étapes</flux:heading>
        <flux:text class="mt-1">
            @if ($useRecoveryCode)
                Saisissez l’un de vos codes de secours. Chaque code ne fonctionne qu’une seule fois.
            @else
                Ouvrez votre application d’authentification et saisissez le code à 6 chiffres affiché pour ce site.
            @endif
        </flux:text>
    </div>

    <form wire:submit="verify" class="flex flex-col gap-5">
        @if ($useRecoveryCode)
            <flux:input wire:model="code" label="Code de secours" placeholder="xxxxx-xxxxx" required autofocus autocomplete="off" />
        @else
            <flux:input wire:model="code" label="Code de vérification" placeholder="123 456" required autofocus
                inputmode="numeric" autocomplete="one-time-code" maxlength="7" class:input="text-center text-lg tracking-[.4em]" />
        @endif

        <flux:button variant="primary" type="submit" class="w-full">Vérifier et se connecter</flux:button>
    </form>

    <div class="flex flex-col items-center gap-2 text-sm">
        <flux:link as="button" type="button" wire:click="toggleRecovery">
            {{ $useRecoveryCode ? 'Utiliser le code de l’application' : 'Téléphone indisponible ? Utiliser un code de secours' }}
        </flux:link>
        <flux:link :href="route('login')" wire:navigate variant="subtle">Revenir à la connexion</flux:link>
    </div>
</div>

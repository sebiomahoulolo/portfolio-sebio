<div class="flex flex-col gap-6">
    <div>
        <flux:heading size="lg">Connexion</flux:heading>
        <flux:text class="mt-1">Accès réservé à l'administration du portfolio.</flux:text>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle" :heading="session('status')" />
    @endif

    <form wire:submit="login" class="flex flex-col gap-5">
        <flux:input wire:model="email" label="Adresse email" type="email" required autofocus autocomplete="email" />

        <div class="relative">
            <flux:input wire:model="password" label="Mot de passe" type="password" required autocomplete="current-password" viewable />
            <flux:link class="absolute end-0 top-0 text-sm" :href="route('password.request')" wire:navigate>Mot de passe oublié ?</flux:link>
        </div>

        <flux:checkbox wire:model="remember" label="Rester connecté" />

        <flux:button variant="primary" type="submit" class="w-full">Se connecter</flux:button>
    </form>
</div>

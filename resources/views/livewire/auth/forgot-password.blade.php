<div class="flex flex-col gap-6">
    <div>
        <flux:heading size="lg">Mot de passe oublié</flux:heading>
        <flux:text class="mt-1">Indiquez votre adresse email pour recevoir un lien de réinitialisation.</flux:text>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle" :heading="session('status')" />
    @endif

    <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-5">
        <flux:input wire:model="email" label="Adresse email" type="email" required autofocus />
        <flux:button variant="primary" type="submit" class="w-full">Envoyer le lien</flux:button>
    </form>

    <flux:link :href="route('login')" wire:navigate class="text-center text-sm">Retour à la connexion</flux:link>
</div>

<div class="flex max-w-2xl flex-col gap-8">
    <x-admin.page-header title="Mon compte" description="Vos informations de connexion et la sécurité de votre compte." />

    <flux:card>
        <form wire:submit="updateProfile" class="flex flex-col gap-5">
            <flux:heading>Informations</flux:heading>
            <flux:input wire:model="name" label="Nom" required />
            <flux:input wire:model="email" label="Adresse email" type="email" required />
            <div><flux:button variant="primary" type="submit">Enregistrer</flux:button></div>
        </form>
    </flux:card>

    <flux:card>
        <form wire:submit="updatePassword" class="flex flex-col gap-5">
            <flux:heading>Mot de passe</flux:heading>
            <flux:input wire:model="current_password" label="Mot de passe actuel" type="password" required autocomplete="current-password" />
            <flux:input wire:model="password" label="Nouveau mot de passe" type="password" required autocomplete="new-password" viewable
                description="12 caractères minimum, avec majuscules, minuscules et chiffres." />
            <flux:input wire:model="password_confirmation" label="Confirmation" type="password" required autocomplete="new-password" />
            <div><flux:button variant="primary" type="submit">Modifier le mot de passe</flux:button></div>
        </form>
    </flux:card>

    {{-- Double authentification --}}
    <flux:card class="flex flex-col gap-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading>Double authentification</flux:heading>
                <flux:text class="mt-1">Un code à 6 chiffres, généré par une application sur votre téléphone, est demandé en plus du mot de passe. Un mot de passe volé ne suffit plus pour entrer.</flux:text>
            </div>
            @if ($twoFactorEnabled)
                <flux:badge color="green" icon="shield-check">Activée</flux:badge>
            @else
                <flux:badge color="amber" icon="shield-exclamation">Désactivée</flux:badge>
            @endif
        </div>

        @if ($recoveryCodes)
            <flux:callout variant="warning" icon="key" heading="Vos codes de secours : notez-les maintenant">
                <flux:callout.text>
                    Ils ne seront plus affichés. Chacun permet une connexion si vous perdez votre téléphone. Gardez-les hors ligne (papier, gestionnaire de mots de passe).
                </flux:callout.text>
            </flux:callout>
            <div class="grid grid-cols-2 gap-2 rounded-lg border border-zinc-200 bg-zinc-50 p-4 font-mono text-sm sm:grid-cols-4 dark:border-zinc-700 dark:bg-zinc-900">
                @foreach ($recoveryCodes as $recoveryCode)
                    <span class="select-all">{{ $recoveryCode }}</span>
                @endforeach
            </div>
            <div><flux:button size="sm" variant="ghost" icon="check" wire:click="$set('recoveryCodes', [])">J’ai noté mes codes</flux:button></div>
        @endif

        @if ($twoFactorEnabled)
            <flux:text>
                Codes de secours restants : <strong>{{ $recoveryCodesLeft }}</strong> sur 8.
                @if ($recoveryCodesLeft <= 2) <span class="text-amber-600 dark:text-amber-400">Pensez à en générer de nouveaux.</span> @endif
            </flux:text>

            <form wire:submit="regenerateRecoveryCodes" class="flex flex-col gap-4 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                <flux:input wire:model="two_factor_password" label="Mot de passe actuel" type="password" autocomplete="current-password"
                    description="Demandé pour générer de nouveaux codes ou désactiver la protection." />
                <div class="flex flex-wrap gap-2">
                    <flux:button type="submit" icon="arrow-path">Nouveaux codes de secours</flux:button>
                    <flux:button variant="danger" icon="shield-exclamation" type="button"
                        data-confirm="Désactiver la double authentification ?"
                        data-confirm-text="Votre compte ne sera plus protégé que par le mot de passe."
                        data-confirm-button="Désactiver"
                        x-on:click="$confirm($el, () => $wire.disableTwoFactor())">Désactiver</flux:button>
                </div>
            </form>
        @elseif ($pendingSecret)
            <ol class="flex list-decimal flex-col gap-4 ps-5 text-sm text-zinc-600 dark:text-zinc-300">
                <li>Installez une application d’authentification : Google Authenticator, Microsoft Authenticator, 2FAS ou Aegis.</li>
                <li>
                    Scannez ce QR code avec l’application :
                    <div class="mt-3 inline-block rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 [&_svg]:size-44">{!! $qrCode !!}</div>
                    <div class="mt-2">Impossible de scanner ? Saisissez cette clé :
                        <code class="select-all break-all rounded bg-zinc-100 px-1.5 py-0.5 font-mono text-xs dark:bg-zinc-900">{{ trim(chunk_split($pendingSecret, 4, ' ')) }}</code>
                    </div>
                </li>
                <li>Saisissez le code à 6 chiffres affiché pour confirmer.</li>
            </ol>
            <form wire:submit="confirmTwoFactor" class="flex flex-col gap-4">
                <flux:input wire:model="two_factor_code" label="Code de vérification" placeholder="123 456" inputmode="numeric" autocomplete="one-time-code" maxlength="7" class="max-w-48" />
                <div class="flex flex-wrap gap-2">
                    <flux:button variant="primary" type="submit" icon="shield-check">Confirmer et activer</flux:button>
                    <flux:button variant="ghost" type="button" wire:click="cancelTwoFactor">Annuler</flux:button>
                </div>
            </form>
        @else
            <div><flux:button variant="primary" icon="shield-check" wire:click="startTwoFactor">Activer la double authentification</flux:button></div>
        @endif
    </flux:card>
</div>

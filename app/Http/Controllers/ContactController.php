<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\ContactMessage;
use App\Notifications\NewContactMessage;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

class ContactController extends Controller
{
    /** Durée minimale (en secondes) qu'un humain met à remplir le formulaire. */
    private const MIN_FILL_SECONDS = 3;

    public function store(ContactRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['website', 'locale', 'started_at']);

        // Spam détecté : on affiche la confirmation habituelle sans rien
        // enregistrer ni notifier, pour ne pas aider le robot à s'adapter.
        if ($this->looksLikeSpam($request, $data)) {
            Log::info('Message de contact ignoré (spam probable)', ['ip_hash' => hash_hmac('sha256', (string) $request->ip(), config('app.key'))]);

            return redirect()->to(url()->previous().'#contact')
                ->with('contact_sent', __('Merci :name ! Votre message a bien été reçu. Je vous réponds sous 48 heures ouvrées.', [
                    'name' => Str::before($data['name'], ' '),
                ]));
        }

        if ($data['type'] !== 'quote') {
            $data['budget'] = null;
        } elseif ($data['budget'] ?? null) {
            $data['budget'] = ContactRequest::BUDGETS[$data['budget']];
        }

        $message = ContactMessage::create($data + [
            'ip_hash' => hash_hmac('sha256', (string) $request->ip(), config('app.key')),
            'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
        ]);

        // Le message est déjà enregistré : un échec d'envoi d'email ne doit
        // pas faire perdre la demande ni afficher une erreur au visiteur.
        try {
            Notification::route('mail', config('portfolio.notify_email'))
                ->notify(new NewContactMessage($message));
            $message->forceFill(['notified_at' => now()])->save();
        } catch (Throwable $e) {
            Log::error('Notification de contact non envoyée', ['message_id' => $message->id, 'error' => $e->getMessage()]);
        }

        return redirect()->to(url()->previous().'#contact')
            ->with('contact_sent', __('Merci :name ! Votre message a bien été reçu. Je vous réponds sous 48 heures ouvrées.', [
                'name' => Str::before($message->name, ' '),
            ]));
    }

    private function looksLikeSpam(ContactRequest $request, array $data): bool
    {
        // Formulaire envoyé sans être affiché, ou rempli trop vite pour un humain.
        try {
            $startedAt = (int) Crypt::decryptString((string) $request->input('started_at'));
        } catch (DecryptException) {
            return true;
        }

        if (now()->timestamp - $startedAt < self::MIN_FILL_SECONDS) {
            return true;
        }

        // Texte généré au hasard : un seul bloc lettres + chiffres + lettres,
        // par exemple « NARETGR278455NEWETREWT ».
        foreach (['name', 'subject', 'message'] as $field) {
            if (preg_match('/^\s*[\p{L}]{3,}\d{3,}[\p{L}]{3,}\s*$/u', (string) ($data[$field] ?? ''))) {
                return true;
            }
        }

        return false;
    }
}

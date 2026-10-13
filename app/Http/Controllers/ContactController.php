<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\ContactMessage;
use App\Notifications\NewContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

class ContactController extends Controller
{
    public function store(ContactRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['website', 'locale']);

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
}

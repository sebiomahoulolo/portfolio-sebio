<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewContactMessage extends Notification
{
    public function __construct(public ContactMessage $message) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $m = $this->message;
        $type = ContactMessage::TYPES[$m->type] ?? $m->type;

        $mail = (new MailMessage)
            ->subject("[{$type}] {$m->subject}")
            ->replyTo($m->email, $m->name)
            ->greeting("Nouveau message : {$type}")
            ->line("**De :** {$m->name} <{$m->email}>");

        if ($m->phone) {
            $mail->line("**Téléphone :** {$m->phone}");
        }
        if ($m->company) {
            $mail->line("**Entreprise :** {$m->company}");
        }
        if ($m->budget) {
            $mail->line("**Budget :** {$m->budget}");
        }

        return $mail
            ->line("**Sujet :** {$m->subject}")
            ->line($m->message)
            ->action('Ouvrir dans l\'administration', route('admin.messages.show', $m))
            ->salutation('Répondez directement à cet email pour écrire à '.$m->name.'.');
    }
}

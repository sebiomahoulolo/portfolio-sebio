<?php

namespace App\Notifications;

use App\Models\Testimonial;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReview extends Notification
{
    public function __construct(public Testimonial $testimonial) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $t = $this->testimonial;

        return (new MailMessage)
            ->subject("[Avis à valider] {$t->author_name} — {$t->rating}/5")
            ->replyTo($t->email, $t->author_name)
            ->greeting('Nouvel avis déposé sur le site')
            ->line("**De :** {$t->author_name}".($t->translate('author_title') ? ' — '.$t->translate('author_title') : ''))
            ->line("**Note :** {$t->rating}/5")
            ->line($t->translate('quote'))
            ->action("Valider ou refuser l'avis", route('admin.testimonials.index'))
            ->salutation("L'avis reste invisible sur le site tant qu'il n'est pas approuvé.");
    }
}

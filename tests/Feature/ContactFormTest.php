<?php

namespace Tests\Feature;

use App\Livewire\Admin\Messages\MessageShow;
use App\Models\ContactMessage;
use App\Models\User;
use App\Notifications\NewContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'quote',
            'name' => 'Awa Dossou',
            'email' => 'awa@example.com',
            'phone' => '+229 01 00 00 00',
            'company' => 'Agence Lumière',
            'subject' => 'Refonte de notre site',
            'budget' => '500k-1500k',
            'message' => 'Nous souhaitons refondre notre site vitrine et ajouter un espace client.',
            'website' => '',
        ], $overrides);
    }

    public function test_valid_message_is_stored_and_owner_is_notified(): void
    {
        Notification::fake();

        $this->from('/')->post(route('contact.store'), $this->payload())
            ->assertRedirect(url('/').'#contact')
            ->assertSessionHas('contact_sent');

        $message = ContactMessage::sole();
        $this->assertSame('Awa Dossou', $message->name);
        $this->assertSame('500 000 à 1 500 000 FCFA', $message->budget);
        $this->assertSame('new', $message->status->value);
        $this->assertNotNull($message->notified_at);
        // L'adresse IP n'est jamais stockée en clair.
        $this->assertNotSame('127.0.0.1', $message->ip_hash);

        Notification::assertSentOnDemand(
            NewContactMessage::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'contact@mahoulolo-sebio.com'
        );
    }

    public function test_budget_is_ignored_for_a_simple_contact(): void
    {
        Notification::fake();

        $this->post(route('contact.store'), $this->payload(['type' => 'contact']));

        $this->assertNull(ContactMessage::sole()->budget);
    }

    public function test_invalid_submission_returns_errors(): void
    {
        $this->from('/')->post(route('contact.store'), $this->payload(['email' => 'pas-un-email', 'message' => 'court']))
            ->assertRedirect(url('/').'#contact')
            ->assertSessionHasErrorsIn('contact', ['email', 'message']);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_honeypot_blocks_bots(): void
    {
        Notification::fake();

        $this->post(route('contact.store'), $this->payload(['website' => 'http://spam.example']))
            ->assertSessionHasErrorsIn('contact', ['website']);

        $this->assertDatabaseCount('contact_messages', 0);
        Notification::assertNothingSent();
    }

    public function test_submissions_are_rate_limited(): void
    {
        Notification::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('contact.store'), $this->payload())->assertRedirect();
        }

        $this->post(route('contact.store'), $this->payload())->assertTooManyRequests();
        $this->assertDatabaseCount('contact_messages', 5);
    }

    public function test_message_is_kept_even_if_the_email_fails(): void
    {
        Notification::shouldReceive('route')->andThrow(new \RuntimeException('SMTP indisponible'));

        $this->from('/')->post(route('contact.store'), $this->payload())->assertSessionHas('contact_sent');

        $this->assertNull(ContactMessage::sole()->notified_at);
    }

    public function test_contact_details_never_appear_on_the_public_site(): void
    {
        ContactMessage::factory()->create(['email' => 'secret-client@example.com']);

        $this->get('/')->assertDontSee('secret-client@example.com');
        $this->get('/projets')->assertDontSee('secret-client@example.com');
    }

    public function test_opening_a_message_marks_it_as_read(): void
    {
        $message = ContactMessage::factory()->create();

        Livewire::actingAs(User::factory()->owner()->create())
            ->test(MessageShow::class, ['message' => $message])
            ->assertSee($message->subject);

        $this->assertSame('read', $message->fresh()->status->value);
        $this->assertNotNull($message->fresh()->read_at);
    }
}

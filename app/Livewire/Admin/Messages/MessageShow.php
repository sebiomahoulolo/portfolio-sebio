<?php

namespace App\Livewire\Admin\Messages;

use App\Enums\MessageStatus;
use App\Models\ContactMessage;
use App\Support\ActivityLogger;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Message')]
class MessageShow extends Component
{
    public ContactMessage $message;

    public function mount(ContactMessage $message): void
    {
        Gate::authorize('manage-contacts');

        $this->message = $message;
        $message->markAsRead();
    }

    public function setStatus(string $status): void
    {
        Gate::authorize('manage-contacts');

        $this->message->update(['status' => MessageStatus::from($status)]);
        $this->message->status = MessageStatus::from($status);

        Flux::toast('Statut mis à jour.', variant: 'success');
    }

    public function delete(): void
    {
        Gate::authorize('manage-contacts');

        ActivityLogger::log('message.deleted', "Message de {$this->message->name} supprimé");
        $this->message->delete();

        Flux::toast('Message supprimé.', variant: 'success');
        $this->redirectRoute('admin.messages.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.messages.message-show');
    }
}

<?php

namespace App\Livewire\Admin\Messages;

use App\Enums\MessageStatus;
use App\Models\ContactMessage;
use App\Support\ActivityLogger;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Messages')]
class MessageIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'inbox')]
    public string $status = 'inbox'; // inbox (nouveaux + lus), new, read, archived

    #[Url(except: '')]
    public string $type = '';

    /** @var list<int> */
    public array $selected = [];

    public function mount(): void
    {
        Gate::authorize('manage-contacts');
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'type'], true)) {
            $this->resetPage();
            $this->selected = [];
        }
    }

    private function query(): Builder
    {
        return ContactMessage::query()
            ->when($this->status === 'inbox', fn (Builder $q) => $q->where('status', '!=', MessageStatus::Archived))
            ->when($this->status !== 'inbox', fn (Builder $q) => $q->where('status', $this->status))
            ->when($this->type !== '', fn (Builder $q) => $q->where('type', $this->type))
            ->when($this->search !== '', fn (Builder $q) => $q->where(function (Builder $q) {
                foreach (['name', 'email', 'company', 'subject'] as $column) {
                    $q->orWhere($column, 'like', '%'.$this->search.'%');
                }
            }));
    }

    #[Computed]
    public function messages()
    {
        return $this->query()->latest()->paginate(20);
    }

    public function bulk(string $action): void
    {
        Gate::authorize('manage-contacts');

        $query = $this->query()->whereKey($this->selected);

        $count = match ($action) {
            'read' => $query->where('status', MessageStatus::New)->update(['status' => MessageStatus::Read, 'read_at' => now()]),
            'archive' => $query->update(['status' => MessageStatus::Archived]),
            'delete' => $query->delete(),
            default => abort(400),
        };

        if ($action === 'delete') {
            ActivityLogger::log('message.deleted', "{$count} message(s) supprimé(s)");
        }

        $this->selected = [];
        unset($this->messages);
        Flux::toast("{$count} message(s) traité(s).", variant: 'success');
    }

    public function render()
    {
        return view('livewire.admin.messages.message-index', [
            'statuses' => MessageStatus::cases(),
        ]);
    }
}

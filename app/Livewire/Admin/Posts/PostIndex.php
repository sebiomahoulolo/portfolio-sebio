<?php

namespace App\Livewire\Admin\Posts;

use App\Enums\PublicationStatus;
use App\Models\Post;
use App\Support\ActivityLogger;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Blog')]
class PostIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Post::class);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status'], true)) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function posts()
    {
        return Post::query()
            ->when($this->search !== '', fn ($q) => $q->whereTranslationLike('title', $this->search))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->latest('updated_at')
            ->paginate(15);
    }

    public function delete(int $id): void
    {
        $post = Post::findOrFail($id);
        $this->authorize('delete', $post);

        ActivityLogger::log('post.deleted', 'Article supprimé : '.$post->translate('title', 'fr'), $post);
        $post->delete();

        Flux::toast('Article placé dans la corbeille.', variant: 'success');
        unset($this->posts);
    }

    public function render()
    {
        return view('livewire.admin.posts.post-index', ['statuses' => PublicationStatus::cases()]);
    }
}

<?php

namespace App\Livewire\Admin\Posts;

use App\Enums\PublicationStatus;
use App\Models\Post;
use App\Support\ActivityLogger;
use Flux\Flux;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class PostForm extends Component
{
    use WithFileUploads;

    private const TRANSLATED = ['title', 'excerpt', 'body', 'seo_title', 'seo_description'];

    #[Locked]
    public ?int $postId = null;

    /** @var array<string, mixed> */
    public array $form = [];

    public string $tags = '';

    public $cover = null;

    public function mount(?Post $post = null): void
    {
        $locales = array_fill_keys(array_keys(config('portfolio.locales')), '');

        if ($post?->exists) {
            $this->authorize('update', $post);
            $this->postId = $post->id;
            $this->form = $post->only(['slug', 'is_indexable']);
            foreach (self::TRANSLATED as $field) {
                $this->form[$field] = array_merge($locales, $post->{$field} ?? []);
            }
            $this->form['status'] = $post->status->value;
            $this->form['published_at'] = $post->published_at?->format('Y-m-d\TH:i');
            $this->tags = implode(', ', $post->tags ?? []);
        } else {
            $this->authorize('create', Post::class);
            $this->form = ['slug' => '', 'is_indexable' => true, 'status' => PublicationStatus::Draft->value, 'published_at' => null];
            foreach (self::TRANSLATED as $field) {
                $this->form[$field] = $locales;
            }
        }
    }

    public function save(): void
    {
        if (blank($this->form['slug'])) {
            $this->form['slug'] = Str::slug((string) ($this->form['title']['fr'] ?? ''));
        }

        $this->validate([
            'form.title.fr' => ['required', 'string', 'max:160'],
            'form.title.*' => ['nullable', 'string', 'max:160'],
            'form.excerpt.*' => ['nullable', 'string', 'max:300'],
            'form.body.*' => ['nullable', 'string', 'max:60000'],
            'form.seo_title.*' => ['nullable', 'string', 'max:70'],
            'form.seo_description.*' => ['nullable', 'string', 'max:160'],
            'form.slug' => ['required', 'alpha_dash:ascii', 'max:160', Rule::unique('posts', 'slug')->ignore($this->postId)],
            'form.status' => ['required', new Enum(PublicationStatus::class)],
            'form.published_at' => ['nullable', 'date'],
            'form.is_indexable' => ['boolean'],
            'tags' => ['nullable', 'string', 'max:300'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [], ['form.title.fr' => 'titre (FR)', 'form.slug' => 'slug', 'cover' => 'image de couverture']);

        $post = $this->postId ? Post::findOrFail($this->postId) : new Post;
        $this->authorize($post->exists ? 'update' : 'create', $post->exists ? $post : Post::class);

        $data = collect($this->form)
            ->only(['slug', 'is_indexable', ...self::TRANSLATED])
            ->map(fn ($value) => is_array($value) ? array_filter($value, fn ($v) => filled($v)) : (blank($value) ? null : $value))
            ->all();
        $data['is_indexable'] = (bool) $this->form['is_indexable'];
        $data['tags'] = collect(explode(',', $this->tags))->map(fn ($t) => trim($t))->filter()->unique()->values()->all();

        $post->fill($data);
        $status = PublicationStatus::from($this->form['status']);
        $publishedAt = $this->form['published_at'] ? Carbon::parse($this->form['published_at']) : null;

        if ($status === PublicationStatus::Published) {
            if ($missing = $post->missingFieldsForPublication()) {
                $this->addError('form.status', 'Impossible de publier : '.implode(', ', $missing).'.');

                return;
            }
            $post->published_at = $publishedAt ?? $post->published_at ?? now();
        } else {
            $post->published_at = $publishedAt ?? $post->published_at;
        }
        $post->status = $status;
        $isNew = ! $post->exists;
        if ($isNew) {
            $post->author_id = auth()->id();
        }
        $post->save();

        if ($this->cover) {
            $old = $post->cover_image;
            $post->update(['cover_image' => $this->cover->store("posts/{$post->id}", config('portfolio.media_disk'))]);
            if ($old) {
                Storage::disk(config('portfolio.media_disk'))->delete($old);
            }
        }

        ActivityLogger::log($isNew ? 'post.created' : 'post.updated', ($isNew ? 'Article créé : ' : 'Article modifié : ').$post->translate('title', 'fr'), $post);
        $this->reset('cover');

        if ($isNew) {
            $this->redirectRoute('admin.posts.edit', ['post' => $post->slug]);

            return;
        }

        Flux::toast('Article enregistré.', variant: 'success');
    }

    public function render()
    {
        return view('livewire.admin.posts.post-form', [
            'statuses' => PublicationStatus::cases(),
            'post' => $this->postId ? Post::find($this->postId) : null,
        ])->title($this->postId ? "Modifier l'article" : 'Nouvel article');
    }
}

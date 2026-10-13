<?php

namespace App\Livewire\Admin;

use App\Models\Testimonial;
use App\Support\ActivityLogger;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
#[Title('Recommandations')]
class TestimonialIndex extends Component
{
    use WithFileUploads;

    public ?int $editingId = null;

    /** @var array<string, mixed> */
    public array $form = [];

    public $avatar = null;

    public function mount(): void
    {
        $this->authorize('viewAny', Testimonial::class);
        $this->resetForm();
    }

    #[Computed]
    public function testimonials()
    {
        // Les avis à valider en premier.
        return Testimonial::orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderBy('sort_order')
            ->orderByDesc('given_at')
            ->get();
    }

    public function approve(int $id): void
    {
        $testimonial = Testimonial::findOrFail($id);
        $this->authorize('publish', $testimonial);

        $testimonial->forceFill(['status' => 'approved', 'is_published' => true])->save();
        ActivityLogger::log('testimonial.approved', 'Avis approuvé : '.$testimonial->author_name, $testimonial);

        Flux::toast('Avis approuvé et publié.', variant: 'success');
        unset($this->testimonials);
    }

    public function reject(int $id): void
    {
        $testimonial = Testimonial::findOrFail($id);
        $this->authorize('publish', $testimonial);

        $testimonial->forceFill(['status' => 'rejected', 'is_published' => false])->save();
        ActivityLogger::log('testimonial.rejected', 'Avis refusé : '.$testimonial->author_name, $testimonial);

        Flux::toast('Avis refusé : il ne sera pas publié.', variant: 'warning');
        unset($this->testimonials);
    }

    public function create(): void
    {
        $this->authorize('create', Testimonial::class);
        $this->resetForm();
        Flux::modal('testimonial-form')->show();
    }

    public function edit(int $id): void
    {
        $testimonial = Testimonial::findOrFail($id);
        $this->authorize('update', $testimonial);

        $this->resetForm();
        $this->editingId = $testimonial->id;
        $locales = array_fill_keys(array_keys(config('portfolio.locales')), '');
        $this->form = [
            'author_name' => $testimonial->author_name,
            'author_title' => array_merge($locales, $testimonial->author_title ?? []),
            'quote' => array_merge($locales, $testimonial->quote ?? []),
            'source' => $testimonial->source,
            'source_url' => (string) $testimonial->source_url,
            'given_at' => $testimonial->given_at?->format('Y-m-d'),
            'rating' => $testimonial->rating,
            'is_published' => $testimonial->is_published,
            'sort_order' => $testimonial->sort_order,
        ];

        Flux::modal('testimonial-form')->show();
    }

    public function save(): void
    {
        $this->validate([
            'form.author_name' => ['required', 'string', 'max:120'],
            'form.author_title.*' => ['nullable', 'string', 'max:160'],
            'form.quote.fr' => ['required', 'string', 'max:2000'],
            'form.quote.*' => ['nullable', 'string', 'max:2000'],
            'form.source' => ['required', Rule::in(array_keys(Testimonial::SOURCES))],
            'form.source_url' => ['nullable', 'url:https', 'max:255'],
            'form.given_at' => ['nullable', 'date'],
            'form.rating' => ['nullable', 'integer', 'between:1,5'],
            'form.is_published' => ['boolean'],
            'form.sort_order' => ['required', 'integer', 'min:0'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [], ['form.author_name' => 'nom', 'form.quote.fr' => 'recommandation (FR)']);

        $testimonial = $this->editingId ? Testimonial::findOrFail($this->editingId) : new Testimonial;
        $this->authorize($testimonial->exists ? 'update' : 'create', $testimonial->exists ? $testimonial : Testimonial::class);

        $data = collect($this->form)
            ->map(fn ($value) => is_array($value) ? array_filter($value, fn ($v) => filled($v)) : (blank($value) ? null : $value))
            ->all();
        $data['is_published'] = (bool) $this->form['is_published'];

        if ($this->avatar) {
            if ($testimonial->avatar_path) {
                Storage::disk(config('portfolio.media_disk'))->delete($testimonial->avatar_path);
            }
            $data['avatar_path'] = $this->avatar->store('testimonials', config('portfolio.media_disk'));
        }

        $testimonial->fill($data);
        // Un avis créé ou modifié ici par l'administrateur est validé d'office.
        $testimonial->status = $data['is_published'] ? 'approved' : ($testimonial->status ?? 'approved');
        $testimonial->save();
        ActivityLogger::log('testimonial.saved', 'Recommandation enregistrée : '.$testimonial->author_name, $testimonial);

        Flux::modal('testimonial-form')->close();
        Flux::toast('Recommandation enregistrée.', variant: 'success');
        unset($this->testimonials);
    }

    public function delete(int $id): void
    {
        $testimonial = Testimonial::findOrFail($id);
        $this->authorize('delete', $testimonial);

        if ($testimonial->avatar_path) {
            Storage::disk(config('portfolio.media_disk'))->delete($testimonial->avatar_path);
        }
        ActivityLogger::log('testimonial.deleted', 'Recommandation supprimée : '.$testimonial->author_name);
        $testimonial->delete();

        Flux::toast('Recommandation supprimée.', variant: 'success');
        unset($this->testimonials);
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->reset('editingId', 'avatar');
        $locales = array_fill_keys(array_keys(config('portfolio.locales')), '');
        $this->form = [
            'author_name' => '', 'author_title' => $locales, 'quote' => $locales,
            'source' => 'linkedin', 'source_url' => '', 'given_at' => now()->format('Y-m-d'), 'rating' => null,
            'is_published' => true, 'sort_order' => (int) Testimonial::max('sort_order') + 1,
        ];
    }

    public function render()
    {
        return view('livewire.admin.testimonial-index');
    }
}

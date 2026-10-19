<?php

namespace App\Livewire\Admin\Projects;

use App\Enums\PublicationStatus;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\Technology;
use App\Support\ActivityLogger;
use Flux\Flux;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class ProjectForm extends Component
{
    use WithFileUploads;

    private const TRANSLATED = ['title', 'excerpt', 'description', 'context', 'challenge', 'solution', 'results', 'role', 'cover_alt', 'seo_title', 'seo_description'];

    private const IMAGE_RULES = ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=4000,max_height=4000'];

    #[Locked]
    public ?int $projectId = null;

    /** @var array<string, mixed> */
    public array $form = [];

    /** @var list<int> */
    public array $technologyIds = [];

    public $cover = null;

    /** @var array<int, mixed> */
    public array $gallery = [];

    public bool $slugTouched = false;

    /** @var list<array{value: string, label: array<string, string>}> chiffres clés de l'étude de cas */
    public array $metrics = [];

    public function mount(?Project $project = null): void
    {
        $locales = array_fill_keys(array_keys(config('portfolio.locales')), '');

        if ($project?->exists) {
            $this->authorize('update', $project);
            $this->projectId = $project->id;
            $this->slugTouched = true;

            $this->form = $project->only([
                'slug', 'slug_en', 'category', 'client', 'period', 'demo_url', 'repository_url', 'sort_order', 'is_featured', 'is_indexable',
            ]);
            foreach (self::TRANSLATED as $field) {
                $this->form[$field] = array_merge($locales, $project->{$field} ?? []);
            }
            $this->form['status'] = $project->status->value;
            $this->form['published_at'] = $project->published_at?->format('Y-m-d\TH:i');
            $this->technologyIds = $project->technologies()->pluck('technologies.id')->all();
            $this->metrics = collect($project->metrics ?? [])
                ->map(fn ($m) => ['value' => (string) ($m['value'] ?? ''), 'label' => array_merge($locales, $m['label'] ?? [])])
                ->values()->all();
        } else {
            $this->authorize('create', Project::class);

            $this->form = [
                'slug' => '', 'slug_en' => '', 'category' => '', 'client' => '', 'period' => '',
                'demo_url' => '', 'repository_url' => '',
                'sort_order' => (int) Project::max('sort_order') + 1,
                'is_featured' => false, 'is_indexable' => true,
                'status' => PublicationStatus::Draft->value,
                'published_at' => null,
            ];
            foreach (self::TRANSLATED as $field) {
                $this->form[$field] = $locales;
            }
        }
    }

    #[Computed]
    public function project(): ?Project
    {
        return $this->projectId ? Project::with('images')->find($this->projectId) : null;
    }

    #[Computed]
    public function technologies()
    {
        return Technology::query()
            ->orderByRaw("FIELD(category, 'frontend', 'backend', 'tools', 'other')")
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');
    }

    #[Computed]
    public function categories()
    {
        return Project::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');
    }

    public function updatedFormTitle($value, $key): void
    {
        if ($key === 'fr' && ! $this->slugTouched) {
            $this->form['slug'] = Str::slug($value);
        }
    }

    public function updatedFormSlug(): void
    {
        $this->slugTouched = true;
    }

    protected function rules(): array
    {
        $translated = fn (int $max, bool $requiredFr = false) => collect(config('portfolio.locales'))
            ->keys()
            ->mapWithKeys(fn ($code) => ["form.%s.{$code}" => [
                ($requiredFr && $code === config('portfolio.default_locale')) ? 'required' : 'nullable',
                'string',
                "max:{$max}",
            ]]);

        $rules = [
            'form.slug' => ['required', 'alpha_dash:ascii', 'max:160', Rule::unique('projects', 'slug')->ignore($this->projectId)],
            'form.slug_en' => ['nullable', 'alpha_dash:ascii', 'max:160', Rule::unique('projects', 'slug_en')->ignore($this->projectId)],
            'form.category' => ['nullable', 'string', 'max:80'],
            'form.client' => ['nullable', 'string', 'max:120'],
            'form.period' => ['nullable', 'string', 'max:60'],
            // Liens publics : HTTPS uniquement.
            'form.demo_url' => ['nullable', 'url:https', 'max:255'],
            'form.repository_url' => ['nullable', 'url:https', 'max:255'],
            'form.sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'form.is_featured' => ['boolean'],
            'form.is_indexable' => ['boolean'],
            'form.status' => ['required', new Enum(PublicationStatus::class)],
            'form.published_at' => ['nullable', 'date'],
            'technologyIds' => ['array'],
            'technologyIds.*' => ['integer', 'exists:technologies,id'],
            'cover' => ['nullable', ...self::IMAGE_RULES],
            'gallery' => ['array', 'max:12'],
            'gallery.*' => self::IMAGE_RULES,
            'metrics' => ['array', 'max:6'],
            'metrics.*.value' => ['nullable', 'string', 'max:20'],
            'metrics.*.label.*' => ['nullable', 'string', 'max:80'],
        ];

        $limits = ['title' => [160, true], 'excerpt' => [300], 'description' => [20000], 'role' => [300],
            'context' => [5000], 'challenge' => [5000], 'solution' => [8000], 'results' => [5000], 'cover_alt' => [160], 'seo_title' => [70], 'seo_description' => [160]];

        foreach ($limits as $field => $args) {
            foreach ($translated(...$args) as $pattern => $fieldRules) {
                $rules[sprintf($pattern, $field)] = $fieldRules;
            }
        }

        return $rules;
    }

    protected function validationAttributes(): array
    {
        return [
            'form.title.fr' => 'titre (FR)',
            'form.slug' => 'slug',
            'form.slug_en' => 'adresse anglaise',
            'form.demo_url' => 'lien de démonstration',
            'form.repository_url' => 'lien du dépôt',
            'cover' => 'image de couverture',
            'gallery.*' => 'image de galerie',
        ];
    }

    public function save(): void
    {
        if (blank($this->form['slug'])) {
            $this->form['slug'] = Str::slug((string) ($this->form['title']['fr'] ?? ''));
        }

        $this->validate();

        $project = $this->projectId ? Project::findOrFail($this->projectId) : new Project;
        $this->authorize($project->exists ? 'update' : 'create', $project->exists ? $project : Project::class);

        $data = collect($this->form)->only([
            'slug', 'slug_en', 'category', 'client', 'period', 'demo_url', 'repository_url', 'sort_order', 'is_featured', 'is_indexable',
            ...self::TRANSLATED,
        ])->map(fn ($value) => is_array($value) ? array_filter($value, fn ($v) => filled($v)) : (blank($value) ? null : $value))->all();

        $data['sort_order'] ??= 0;
        $data['is_featured'] = (bool) $this->form['is_featured'];
        $data['is_indexable'] = (bool) $this->form['is_indexable'];
        $data['metrics'] = collect($this->metrics)
            ->filter(fn ($m) => filled($m['value'] ?? null))
            ->map(fn ($m) => ['value' => trim($m['value']), 'label' => array_filter($m['label'] ?? [], fn ($v) => filled($v))])
            ->values()
            ->all() ?: null;

        $wasPublished = $project->exists && $project->status === PublicationStatus::Published;
        $requestedStatus = PublicationStatus::from($this->form['status']);

        $project->fill($data);

        // Un brouillon peut être enregistré incomplet ; la publication exige les informations essentielles.
        if ($requestedStatus === PublicationStatus::Published) {
            $this->authorize('publish', $project->exists ? $project : Project::class);

            if ($missing = $project->missingFieldsForPublication()) {
                $this->addError('form.status', 'Impossible de publier : '.implode(', ', $missing).'.');

                return;
            }

            $project->published_at = $this->form['published_at'] ? Carbon::parse($this->form['published_at']) : ($project->published_at ?? now());
        } else {
            $project->published_at = $this->form['published_at'] ? Carbon::parse($this->form['published_at']) : $project->published_at;
        }

        $project->status = $requestedStatus;

        DB::transaction(function () use ($project) {
            $project->save();
            $project->technologies()->sync($this->technologyIds);

            if ($this->cover) {
                $old = $project->cover_image;
                $project->update(['cover_image' => $this->cover->store("projects/{$project->id}", config('portfolio.media_disk'))]);
                if ($old) {
                    Storage::disk(config('portfolio.media_disk'))->delete($old);
                }
            }

            $nextOrder = (int) $project->images()->max('sort_order') + 1;
            foreach ($this->gallery as $file) {
                $project->images()->create([
                    'path' => $file->store("projects/{$project->id}/gallery", config('portfolio.media_disk')),
                    'sort_order' => $nextOrder++,
                ]);
            }
        });

        $isNew = $this->projectId === null;
        $title = $project->translate('title');

        if ($isNew) {
            ActivityLogger::log('project.created', "Projet créé : {$title}", $project);
        } elseif (! $wasPublished && $project->status === PublicationStatus::Published) {
            ActivityLogger::log('project.published', "Projet publié : {$title}", $project);
        } else {
            ActivityLogger::log('project.updated', "Projet modifié : {$title}", $project);
        }

        // Les enregistrements suivants modifient ce projet au lieu d'en créer un nouveau.
        $this->projectId = $project->id;
        $this->slugTouched = true;
        $this->reset('cover', 'gallery');
        unset($this->project);

        if ($isNew) {
            session()->flash('project_created', true);
            $this->redirectRoute('admin.projects.edit', ['project' => $project->slug]);

            return;
        }

        Flux::toast('Projet enregistré.', variant: 'success');
    }

    public function addMetric(): void
    {
        if (count($this->metrics) < 6) {
            $this->metrics[] = ['value' => '', 'label' => array_fill_keys(array_keys(config('portfolio.locales')), '')];
        }
    }

    public function removeMetric(int $index): void
    {
        unset($this->metrics[$index]);
        $this->metrics = array_values($this->metrics);
    }

    public function removeCover(): void
    {
        $project = Project::findOrFail($this->projectId);
        $this->authorize('update', $project);

        if ($project->cover_image) {
            Storage::disk(config('portfolio.media_disk'))->delete($project->cover_image);
            $project->update(['cover_image' => null]);
        }
    }

    public function removeImage(int $imageId): void
    {
        $image = ProjectImage::where('project_id', $this->projectId)->findOrFail($imageId);
        $this->authorize('update', $image->project);

        Storage::disk(config('portfolio.media_disk'))->delete($image->path);
        $image->delete();
        unset($this->project);
    }

    public function moveImage(int $imageId, string $direction): void
    {
        $project = Project::findOrFail($this->projectId);
        $this->authorize('update', $project);

        $images = $project->images()->get()->values();
        $index = $images->search(fn ($image) => $image->id === $imageId);
        $swap = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index === false || ! isset($images[$swap])) {
            return;
        }

        DB::transaction(function () use ($images, $index, $swap) {
            $ordered = $images->all();
            [$ordered[$index], $ordered[$swap]] = [$ordered[$swap], $ordered[$index]];
            foreach (array_values($ordered) as $position => $image) {
                $image->update(['sort_order' => $position]);
            }
        });
        unset($this->project);
    }

    public function updateImageAlt(int $imageId, string $locale, string $value): void
    {
        $image = ProjectImage::where('project_id', $this->projectId)->findOrFail($imageId);
        $this->authorize('update', $image->project);

        abort_unless(array_key_exists($locale, config('portfolio.locales')), 422);

        $alt = $image->alt ?? [];
        $alt[$locale] = mb_substr(trim($value), 0, 160);
        $image->update(['alt' => array_filter($alt)]);
    }

    public function render()
    {
        return view('livewire.admin.projects.project-form', [
            'statuses' => PublicationStatus::cases(),
        ])->title($this->projectId ? 'Modifier le projet' : 'Nouveau projet');
    }
}

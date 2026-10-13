<?php

namespace App\Livewire\Admin\Services;

use App\Models\Service;
use App\Support\ActivityLogger;
use Flux\Flux;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ServiceForm extends Component
{
    private const TRANSLATED = ['title', 'summary', 'description', 'price_label', 'seo_description'];

    #[Locked]
    public ?int $serviceId = null;

    /** @var array<string, mixed> */
    public array $form = [];

    /** @var array<string, string> « ce qui est inclus », une ligne par élément et par langue */
    public array $features = [];

    public function mount(?Service $service = null): void
    {
        $locales = array_fill_keys(array_keys(config('portfolio.locales')), '');

        if ($service?->exists) {
            $this->authorize('update', $service);
            $this->serviceId = $service->id;
            $this->form = $service->only(['slug', 'icon', 'sort_order', 'is_published']);
            foreach (self::TRANSLATED as $field) {
                $this->form[$field] = array_merge($locales, $service->{$field} ?? []);
            }
            foreach (array_keys($locales) as $code) {
                $this->features[$code] = implode("\n", ($service->features ?? [])[$code] ?? []);
            }
        } else {
            $this->authorize('create', Service::class);
            $this->form = ['slug' => '', 'icon' => 'fas fa-code', 'sort_order' => (int) Service::max('sort_order') + 1, 'is_published' => true];
            foreach (self::TRANSLATED as $field) {
                $this->form[$field] = $locales;
            }
            $this->features = $locales;
        }
    }

    public function save(): void
    {
        if (blank($this->form['slug'])) {
            $this->form['slug'] = Str::slug((string) ($this->form['title']['fr'] ?? ''));
        }

        $this->validate([
            'form.title.fr' => ['required', 'string', 'max:120'],
            'form.title.*' => ['nullable', 'string', 'max:120'],
            'form.summary.*' => ['nullable', 'string', 'max:220'],
            'form.description.*' => ['nullable', 'string', 'max:8000'],
            'form.price_label.*' => ['nullable', 'string', 'max:80'],
            'form.seo_description.*' => ['nullable', 'string', 'max:160'],
            'form.slug' => ['required', 'alpha_dash:ascii', 'max:160', Rule::unique('services', 'slug')->ignore($this->serviceId)],
            'form.icon' => ['nullable', 'string', 'max:60', 'regex:/^(fa[bsr]?|fa-(solid|brands|regular))( fa-[a-z0-9-]+)+$/'],
            'form.sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'form.is_published' => ['boolean'],
            'features.*' => ['nullable', 'string', 'max:2000'],
        ], [], ['form.title.fr' => 'titre (FR)', 'form.slug' => 'slug', 'form.icon' => 'icône']);

        $service = $this->serviceId ? Service::findOrFail($this->serviceId) : new Service;
        $this->authorize($service->exists ? 'update' : 'create', $service->exists ? $service : Service::class);

        $data = collect($this->form)
            ->map(fn ($value) => is_array($value) ? array_filter($value, fn ($v) => filled($v)) : (blank($value) ? null : $value))
            ->all();
        $data['is_published'] = (bool) $this->form['is_published'];
        $data['features'] = collect($this->features)
            ->map(fn ($text) => collect(preg_split('/\R/', (string) $text))->map(fn ($line) => trim($line))->filter()->values()->all())
            ->filter()
            ->all();

        $isNew = ! $service->exists;
        $service->fill($data)->save();

        ActivityLogger::log($isNew ? 'service.created' : 'service.updated', ($isNew ? 'Service créé : ' : 'Service modifié : ').$service->translate('title', 'fr'), $service);

        if ($isNew) {
            $this->redirectRoute('admin.services.edit', $service);

            return;
        }

        Flux::toast('Service enregistré.', variant: 'success');
    }

    public function render()
    {
        return view('livewire.admin.services.service-form')
            ->title($this->serviceId ? 'Modifier le service' : 'Nouveau service');
    }
}

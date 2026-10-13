<?php

namespace App\Livewire\Admin;

use App\Models\Certification;
use App\Models\Education;
use App\Models\Experience;
use App\Support\ActivityLogger;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Parcours public : expériences, formations et certifications, gérées
 * depuis une seule page avec une fenêtre d'édition par type.
 */
#[Layout('components.layouts.admin')]
#[Title('Parcours')]
class CareerIndex extends Component
{
    private const MODELS = [
        'experience' => Experience::class,
        'education' => Education::class,
        'certification' => Certification::class,
    ];

    private const LABELS = [
        'experience' => 'expérience',
        'education' => 'formation',
        'certification' => 'certification',
    ];

    #[Locked]
    public string $kind = 'experience';

    #[Locked]
    public ?int $editingId = null;

    /** @var array<string, mixed> */
    public array $form = [];

    public function mount(): void
    {
        $this->authorize('viewAny', Experience::class);
    }

    #[Computed]
    public function experiences()
    {
        return Experience::orderByRaw('ended_at IS NOT NULL')->orderByDesc('started_at')->get();
    }

    #[Computed]
    public function educations()
    {
        return Education::orderBy('sort_order')->get();
    }

    #[Computed]
    public function certifications()
    {
        return Certification::orderByDesc('issued_at')->get();
    }

    public function create(string $kind): void
    {
        abort_unless(isset(self::MODELS[$kind]), 404);
        $this->authorize('create', self::MODELS[$kind]);

        $this->resetValidation();
        $this->kind = $kind;
        $this->editingId = null;
        $this->form = $this->blankForm($kind);

        Flux::modal('career-form')->show();
    }

    public function edit(string $kind, int $id): void
    {
        abort_unless(isset(self::MODELS[$kind]), 404);
        $model = self::MODELS[$kind]::findOrFail($id);
        $this->authorize('update', $model);

        $this->resetValidation();
        $this->kind = $kind;
        $this->editingId = $model->id;
        $this->form = $this->blankForm($kind);

        foreach ($this->form as $key => $default) {
            $value = $model->{$key};
            if (is_array($default)) {
                $this->form[$key] = array_merge($default, $value ?? []);
            } elseif ($value instanceof \DateTimeInterface) {
                $this->form[$key] = $value->format('Y-m-d');
            } else {
                $this->form[$key] = $value ?? $default;
            }
        }
        if ($kind === 'experience') {
            $this->form['is_current'] = $model->ended_at === null;
        }

        Flux::modal('career-form')->show();
    }

    public function save(): void
    {
        $class = self::MODELS[$this->kind];
        $model = $this->editingId ? $class::findOrFail($this->editingId) : new $class;
        $this->authorize($model->exists ? 'update' : 'create', $model->exists ? $model : $class);

        $this->validate($this->rules(), [], [
            'form.title.fr' => 'intitulé (FR)', 'form.degree.fr' => 'diplôme (FR)', 'form.name.fr' => 'nom (FR)',
            'form.company' => 'entreprise', 'form.institution' => 'établissement', 'form.issuer' => 'organisme',
            'form.started_at' => 'date de début', 'form.ended_at' => 'date de fin',
        ]);

        $data = collect($this->form)
            ->except('is_current')
            ->map(fn ($value) => is_array($value) ? array_filter($value, fn ($v) => filled($v)) : (blank($value) ? null : $value))
            ->all();
        $data['is_visible'] = (bool) ($this->form['is_visible'] ?? true);

        if ($this->kind === 'experience' && ($this->form['is_current'] ?? false)) {
            $data['ended_at'] = null;
        }

        $model->fill($data)->save();
        ActivityLogger::log("{$this->kind}.saved", 'Parcours mis à jour : '.self::LABELS[$this->kind], $model);

        Flux::modal('career-form')->close();
        Flux::toast('Enregistré.', variant: 'success');
        unset($this->experiences, $this->educations, $this->certifications);
    }

    public function delete(string $kind, int $id): void
    {
        abort_unless(isset(self::MODELS[$kind]), 404);
        $model = self::MODELS[$kind]::findOrFail($id);
        $this->authorize('delete', $model);

        ActivityLogger::log("{$kind}.deleted", 'Élément de parcours supprimé : '.self::LABELS[$kind]);
        $model->delete();

        Flux::toast('Supprimé.', variant: 'success');
        unset($this->experiences, $this->educations, $this->certifications);
    }

    private function rules(): array
    {
        $common = ['form.is_visible' => ['boolean'], 'form.sort_order' => ['nullable', 'integer', 'min:0']];

        return match ($this->kind) {
            'experience' => $common + [
                'form.title.fr' => ['required', 'string', 'max:160'],
                'form.title.*' => ['nullable', 'string', 'max:160'],
                'form.company' => ['required', 'string', 'max:160'],
                'form.location' => ['nullable', 'string', 'max:120'],
                'form.employment_type' => ['nullable', Rule::in(array_keys(Experience::EMPLOYMENT_TYPES))],
                'form.description.*' => ['nullable', 'string', 'max:2000'],
                'form.started_at' => ['required', 'date'],
                'form.ended_at' => ['nullable', 'date', 'after_or_equal:form.started_at'],
                'form.is_current' => ['boolean'],
            ],
            'education' => $common + [
                'form.degree.fr' => ['required', 'string', 'max:160'],
                'form.degree.*' => ['nullable', 'string', 'max:160'],
                'form.field.*' => ['nullable', 'string', 'max:160'],
                'form.institution' => ['required', 'string', 'max:160'],
                'form.location' => ['nullable', 'string', 'max:120'],
                'form.start_year' => ['nullable', 'integer', 'between:1950,2100'],
                'form.end_year' => ['nullable', 'integer', 'between:1950,2100', 'gte:form.start_year'],
            ],
            'certification' => $common + [
                'form.name.fr' => ['required', 'string', 'max:200'],
                'form.name.*' => ['nullable', 'string', 'max:200'],
                'form.issuer' => ['required', 'string', 'max:160'],
                'form.issued_at' => ['nullable', 'date'],
                'form.credential_url' => ['nullable', 'url:https', 'max:255'],
            ],
        };
    }

    private function blankForm(string $kind): array
    {
        $locales = array_fill_keys(array_keys(config('portfolio.locales')), '');

        return match ($kind) {
            'experience' => [
                'title' => $locales, 'company' => '', 'location' => '', 'employment_type' => 'full_time',
                'description' => $locales, 'started_at' => '', 'ended_at' => '', 'is_current' => false,
                'is_visible' => true, 'sort_order' => 0,
            ],
            'education' => [
                'degree' => $locales, 'field' => $locales, 'institution' => '', 'location' => '',
                'start_year' => '', 'end_year' => '', 'is_visible' => true, 'sort_order' => (int) Education::max('sort_order') + 1,
            ],
            'certification' => [
                'name' => $locales, 'issuer' => '', 'issued_at' => '', 'credential_url' => '',
                'is_visible' => true, 'sort_order' => 0,
            ],
        };
    }

    public function render()
    {
        return view('livewire.admin.career-index', ['labels' => self::LABELS]);
    }
}

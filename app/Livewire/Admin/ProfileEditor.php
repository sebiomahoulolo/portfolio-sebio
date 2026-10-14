<?php

namespace App\Livewire\Admin;

use App\Models\ProfileSetting;
use App\Support\ActivityLogger;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
#[Title('Profil public')]
class ProfileEditor extends Component
{
    use WithFileUploads;

    private const TRANSLATED = ['headline', 'subtitle', 'tagline', 'intro', 'about', 'objective', 'current_position', 'featured_project', 'seo_title', 'seo_description'];

    private const SOCIAL = ['linkedin' => 'LinkedIn', 'github' => 'GitHub', 'facebook' => 'Facebook', 'x' => 'X (Twitter)'];

    /** @var array<string, mixed> */
    public array $form = [];

    /** @var array<string, string> */
    public array $social = [];

    /** @var array<string, string> liste séparée par des virgules, par langue */
    public array $interests = [];

    /** @var array<string, string> badges d'expertise, séparés par des virgules, par langue */
    public array $expertise = [];

    /** Langues parlées, une par ligne « Langue : niveau », par langue du site. */
    public array $languages = [];

    public $avatar = null;

    public $cv = null;

    public $ogImage = null;

    public function mount(): void
    {
        Gate::authorize('manage-settings');

        $profile = ProfileSetting::current();
        $locales = array_fill_keys(array_keys(config('portfolio.locales')), '');

        $this->form = $profile->only(['name', 'featured_project_url', 'email', 'phone', 'whatsapp', 'location', 'is_available', 'years_experience']);
        $this->form['is_available'] = (bool) ($this->form['is_available'] ?? true);
        foreach (self::TRANSLATED as $field) {
            $this->form[$field] = array_merge($locales, $profile->{$field} ?? []);
        }

        foreach (array_keys(self::SOCIAL) as $network) {
            $this->social[$network] = (string) $profile->social($network);
        }

        foreach (array_keys($locales) as $code) {
            $this->interests[$code] = implode(', ', ($profile->interests ?? [])[$code] ?? []);
            $this->expertise[$code] = implode(', ', ($profile->expertise ?? [])[$code] ?? []);
        }
        // La version anglaise est préremplie avec la traduction automatique : il suffit de la relire.
        foreach (array_keys($locales) as $code) {
            $this->languages[$code] = collect($profile->languagesFor($code))
                ->map(fn ($l) => trim($l['name'].' : '.$l['level'], ' :'))
                ->implode("\n");
        }
    }

    public function save(): void
    {
        Gate::authorize('manage-settings');

        $rules = [
            'form.name' => ['required', 'string', 'max:120'],
            'form.featured_project_url' => ['nullable', 'url:https', 'max:255'],
            'form.email' => ['required', 'email', 'max:190'],
            'form.phone' => ['nullable', 'string', 'max:40'],
            'form.whatsapp' => ['nullable', 'regex:/^\d{8,15}$/'],
            'form.location' => ['nullable', 'string', 'max:120'],
            'form.is_available' => ['boolean'],
            'social.*' => ['nullable', 'url:https', 'max:255'],
            'interests.*' => ['nullable', 'string', 'max:500'],
            'expertise.*' => ['nullable', 'string', 'max:500'],
            'form.years_experience' => ['nullable', 'integer', 'between:0,60'],
            'form.tagline.*' => ['nullable', 'string', 'max:160'],
            'languages.*' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'cv' => ['nullable', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:5120'],
            'ogImage' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:3072'],
            'form.headline.fr' => ['required', 'string', 'max:120'],
            'form.seo_title.*' => ['nullable', 'string', 'max:70'],
            'form.seo_description.*' => ['nullable', 'string', 'max:160'],
        ];
        foreach (['subtitle' => 160, 'intro' => 1500, 'about' => 3000, 'objective' => 600, 'current_position' => 200, 'featured_project' => 600] as $field => $max) {
            $rules["form.{$field}.*"] = ['nullable', 'string', "max:{$max}"];
        }

        $this->validate($rules, [
            'form.whatsapp.regex' => 'Numéro WhatsApp au format international, chiffres uniquement (ex. 2290165603040).',
        ], [
            'form.headline.fr' => 'titre professionnel',
            'social.*' => 'lien',
        ]);

        $profile = ProfileSetting::query()->first() ?? new ProfileSetting;

        $data = collect($this->form)
            ->map(fn ($value) => is_array($value) ? array_filter($value, fn ($v) => filled($v)) : (blank($value) ? null : $value))
            // « plus de 3 ans » devient « plus de {ans} ans » : le texte suit le parcours.
            ->map(fn ($value) => is_array($value) ? array_map(fn ($v) => is_string($v) ? ProfileSetting::withYearsPlaceholder($v) : $v, $value) : $value)
            ->all();
        $data['is_available'] = (bool) $this->form['is_available'];
        $data['social_links'] = array_filter($this->social, fn ($v) => filled($v));
        $toList = fn (array $byLocale) => collect($byLocale)
            ->map(fn ($text) => collect(explode(',', (string) $text))->map(fn ($i) => trim($i))->filter()->values()->all())
            ->filter()
            ->all();
        $data['interests'] = $toList($this->interests);
        $data['expertise'] = $toList($this->expertise);
        $data['spoken_languages'] = collect($this->languages)
            ->map(fn ($text) => collect(preg_split('/\R/', (string) $text))
                ->map(fn ($line) => array_map('trim', explode(':', $line, 2)))
                ->filter(fn ($parts) => filled($parts[0]))
                ->map(fn ($parts) => ['name' => $parts[0], 'level' => $parts[1] ?? ''])
                ->values()
                ->all())
            ->filter()
            ->all();

        $disk = Storage::disk(config('portfolio.media_disk'));

        foreach (['avatar' => ['avatar_path', 'profile'], 'cv' => ['cv_path', 'cv'], 'ogImage' => ['og_image_path', 'profile']] as $upload => [$column, $directory]) {
            if ($this->{$upload}) {
                if ($profile->{$column}) {
                    $disk->delete($profile->{$column});
                }
                $data[$column] = $upload === 'cv'
                    ? $this->cv->storeAs($directory, 'CV-'.str($data['name'])->slug().'.pdf', config('portfolio.media_disk'))
                    : $this->{$upload}->store($directory, config('portfolio.media_disk'));
            }
        }

        $profile->fill($data)->save();

        ActivityLogger::log('profile.updated', 'Profil public mis à jour', $profile);
        $this->reset('avatar', 'cv', 'ogImage');

        Flux::toast('Profil enregistré.', variant: 'success');
    }

    public function render()
    {
        return view('livewire.admin.profile-editor', [
            'profile' => ProfileSetting::current(),
            'networks' => self::SOCIAL,
        ]);
    }
}

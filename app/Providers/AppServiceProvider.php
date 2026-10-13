<?php

namespace App\Providers;

use App\Models\Certification;
use App\Models\ContactMessage;
use App\Models\Education;
use App\Models\Experience;
use App\Models\ProfileSetting;
use App\Models\Project;
use App\Models\Service;
use App\Models\Technology;
use App\Models\Testimonial;
use App\Models\User;
use App\Policies\ContentPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        Relation::enforceMorphMap([
            'user' => User::class,
            'project' => Project::class,
            'technology' => Technology::class,
            'service' => Service::class,
            'testimonial' => Testimonial::class,
            'experience' => Experience::class,
            'education' => Education::class,
            'certification' => Certification::class,
            'contact_message' => ContactMessage::class,
            'profile' => ProfileSetting::class,
        ]);

        $this->configureAuthorization();
        $this->configureRateLimiting();
    }

    private function configureAuthorization(): void
    {
        // Un compte désactivé n'a plus aucun droit ; le propriétaire les a tous.
        Gate::before(function (User $user) {
            if (! $user->is_active) {
                return false;
            }

            return $user->isOwner() ? true : null;
        });

        foreach ([Project::class, Technology::class, Service::class, Testimonial::class, Experience::class, Education::class, Certification::class] as $model) {
            Gate::policy($model, ContentPolicy::class);
        }

        Gate::define('access-admin', fn (User $user) => $user->is_active);

        // Réservé au propriétaire : données personnelles des contacts,
        // profil public et journal d'activité.
        foreach (['manage-contacts', 'manage-settings', 'view-activity'] as $ability) {
            Gate::define($ability, fn (User $user) => false);
        }
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perHour(config('portfolio.contact_rate_limit'))->by($request->ip());
        });
    }
}

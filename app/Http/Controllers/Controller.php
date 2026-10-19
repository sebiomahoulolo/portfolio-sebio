<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;

abstract class Controller
{
    /**
     * Retrouve un contenu à adresse traduite (article, service, projet).
     *
     * Une adresse qui n'est pas celle de la langue affichée (ancien lien
     * /en/blog/<slug-français>) est redirigée en 301 vers la bonne. Les adresses
     * des autres langues sont transmises au sélecteur de langue et au hreflang.
     */
    protected function findLocalized(Builder $query, string $slug, string $route): Model|RedirectResponse
    {
        $locale = app()->getLocale();
        $model = $query->getModel()::findBySlugFor($query, $slug, $locale) ?? abort(404);

        if ($model->slugFor($locale) !== $slug) {
            return redirect()->to(lroute($route, ['slug' => $model->slugFor($locale)]), 301);
        }

        request()->attributes->set('localized_parameters', $model->localizedRouteParameters());

        return $model;
    }
}

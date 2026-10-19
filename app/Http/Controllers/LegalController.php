<?php

namespace App\Http\Controllers;

use App\Models\ProfileSetting;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Mentions légales et politique de confidentialité. Le texte est rédigé par
 * langue (resources/views/site/legal/{fr,en}/) et reprend les coordonnées du profil.
 */
class LegalController extends Controller
{
    /** Date de la dernière révision des textes, affichée en tête de page. */
    public const UPDATED_AT = '2026-10-19';

    public function notice(): View
    {
        return $this->page('notice', __('Mentions légales'));
    }

    public function privacy(): View
    {
        return $this->page('privacy', __('Politique de confidentialité'));
    }

    private function page(string $page, string $title): View
    {
        return view('site.legal.show', [
            'profile' => ProfileSetting::current(),
            'page' => $page,
            'title' => $title,
            'updatedAt' => Carbon::parse(self::UPDATED_AT),
            'retention' => config('portfolio.retention'),
        ]);
    }
}

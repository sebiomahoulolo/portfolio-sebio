<?php

namespace App\Http\Controllers;

use App\Models\ProfileSetting;
use App\Models\Testimonial;
use App\Notifications\NewReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

/**
 * Avis déposés par les visiteurs : ils sont enregistrés « en attente » et
 * n'apparaissent sur le site qu'après validation dans l'administration.
 */
class ReviewController extends Controller
{
    public function create(): View
    {
        return view('site.reviews.create', [
            'profile' => ProfileSetting::current(),
            'testimonials' => Testimonial::published()->orderBy('sort_order')->limit(4)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $locale = (string) $request->input('locale');
        if (array_key_exists($locale, config('portfolio.locales'))) {
            app()->setLocale($locale);
        } else {
            $locale = config('portfolio.default_locale');
        }

        $data = $request->validateWithBag('review', [
            'author_name' => ['required', 'string', 'min:2', 'max:120'],
            'author_title' => ['nullable', 'string', 'max:160'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'quote' => ['required', 'string', 'min:20', 'max:1500'],
            'consent' => ['accepted'],
            'locale' => ['nullable', Rule::in(array_keys(config('portfolio.locales')))],
            'website' => ['present', 'max:0'], // piège à robots
        ], [
            'website.max' => __("Votre avis n'a pas pu être envoyé."),
            'website.present' => __("Votre avis n'a pas pu être envoyé."),
            'consent.accepted' => __('Merci d’accepter la publication de votre avis.'),
        ], [
            'author_name' => __('nom'),
            'author_title' => __('fonction'),
            'email' => __('adresse email'),
            'rating' => __('note'),
            'quote' => __('avis'),
        ]);

        $testimonial = new Testimonial([
            'author_name' => strip_tags($data['author_name']),
            'author_title' => filled($data['author_title'] ?? null) ? [$locale => strip_tags($data['author_title'])] : null,
            'quote' => [$locale => strip_tags($data['quote'])],
            'email' => $data['email'],
            'rating' => (int) $data['rating'],
            'source' => 'site',
            'given_at' => now()->toDateString(),
            'is_published' => false,
            'sort_order' => (int) Testimonial::max('sort_order') + 1,
        ]);
        $testimonial->status = 'pending';
        $testimonial->ip_hash = hash_hmac('sha256', (string) $request->ip(), config('app.key'));
        $testimonial->save();

        try {
            Notification::route('mail', config('portfolio.notify_email'))->notify(new NewReview($testimonial));
        } catch (Throwable $e) {
            Log::error('Notification d’avis non envoyée', ['testimonial_id' => $testimonial->id, 'error' => $e->getMessage()]);
        }

        return redirect()->to(lroute('reviews.create', [], $locale).'#avis')
            ->with('review_sent', __('Merci :name ! Votre avis a bien été reçu. Il sera publié après validation.', [
                'name' => strtok($testimonial->author_name, ' '),
            ]));
    }
}

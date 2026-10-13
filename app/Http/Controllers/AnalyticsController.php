<?php

namespace App\Http\Controllers;

use App\Models\PageView;
use App\Support\VisitorContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Reçoit les mesures d'audience envoyées par le navigateur : une page vue
 * à l'arrivée, puis la durée passée sur la page au départ.
 */
class AnalyticsController extends Controller
{
    public function store(Request $request): JsonResponse|Response
    {
        $context = new VisitorContext($request);

        // Robots, administrateurs connectés et visiteurs « Do Not Track » ne sont pas comptés.
        if ($context->isBot() || $request->user() || $request->header('DNT') === '1') {
            return response()->noContent();
        }

        $data = $request->validate([
            'path' => ['required', 'string', 'max:255', 'starts_with:/'],
            'title' => ['nullable', 'string', 'max:190'],
            'referrer' => ['nullable', 'string', 'max:500'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'locale' => ['nullable', 'string', 'max:5'],
        ]);

        $visitorHash = $context->visitorHash();
        $isNewVisitor = ! PageView::where('visitor_hash', $visitorHash)->exists();

        $view = PageView::create([
            'uuid' => (string) Str::uuid(),
            'visitor_hash' => $visitorHash,
            'session_hash' => $context->sessionHash(),
            'path' => $data['path'],
            'title' => isset($data['title']) ? Str::limit($data['title'], 185, '') : null,
            'locale' => array_key_exists($data['locale'] ?? '', config('portfolio.locales')) ? $data['locale'] : null,
            'referrer_host' => $context->referrerHost($data['referrer'] ?? null),
            'country_code' => $context->countryCode($data['timezone'] ?? null),
            'device' => $context->device(),
            'browser' => $context->browser(),
            'os' => $context->os(),
        ]);

        // Le compteur public se met à jour dès l'arrivée d'un nouveau visiteur.
        if ($isNewVisitor) {
            Cache::forget('analytics.public-visitors');
        }

        return response()->json(['id' => $view->uuid], 201);
    }

    public function duration(Request $request, string $uuid): Response
    {
        $seconds = (int) $request->validate(['duration' => ['required', 'integer', 'min:0']])['duration'];

        // Seule la session qui a créé la page vue peut en mettre à jour la durée.
        PageView::where('uuid', $uuid)
            ->where('session_hash', (new VisitorContext($request))->sessionHash())
            ->update(['duration_seconds' => min($seconds, 4 * 3600), 'updated_at' => now()]);

        return response()->noContent();
    }
}

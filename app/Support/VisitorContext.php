<?php

namespace App\Support;

use DateTimeZone;
use Illuminate\Http\Request;
use Throwable;

/**
 * Déduit des informations anonymes sur un visiteur : pays, type d'appareil,
 * navigateur, système, et détecte les robots. Aucune adresse IP n'est conservée.
 */
class VisitorContext
{
    private const BOT_PATTERN = '/bot|crawl|spider|slurp|bing|facebookexternalhit|embedly|preview|lighthouse|headless|python|curl|wget|httpclient|monitor|uptime/i';

    public function __construct(private Request $request) {}

    public function isBot(): bool
    {
        $agent = (string) $this->request->userAgent();

        return $agent === '' || (bool) preg_match(self::BOT_PATTERN, $agent);
    }

    /**
     * Empreinte anonyme qui change chaque jour : elle permet de compter les
     * visiteurs uniques sans pouvoir les suivre d'un jour à l'autre.
     */
    public function visitorHash(): string
    {
        return hash_hmac('sha256', now()->toDateString().'|'.$this->request->ip().'|'.$this->request->userAgent(), config('app.key'));
    }

    public function sessionHash(): string
    {
        return hash_hmac('sha256', $this->request->session()->getId(), config('app.key'));
    }

    /**
     * Pays : en-tête fourni par le CDN s'il existe, sinon fuseau horaire du navigateur.
     */
    public function countryCode(?string $timezone): ?string
    {
        foreach (['CF-IPCountry', 'X-Country-Code', 'X-Geo-Country', 'CloudFront-Viewer-Country'] as $header) {
            $value = strtoupper((string) $this->request->header($header));
            if (preg_match('/^[A-Z]{2}$/', $value) && $value !== 'XX') {
                return $value;
            }
        }

        if ($timezone && in_array($timezone, DateTimeZone::listIdentifiers(), true)) {
            try {
                $code = (new DateTimeZone($timezone))->getLocation()['country_code'] ?? null;

                return $code && $code !== '??' ? $code : null;
            } catch (Throwable) {
                return null;
            }
        }

        return null;
    }

    public function device(): string
    {
        $agent = (string) $this->request->userAgent();

        return match (true) {
            (bool) preg_match('/ipad|tablet|kindle|silk/i', $agent) => 'tablet',
            (bool) preg_match('/mobi|android|iphone|ipod/i', $agent) => 'mobile',
            default => 'desktop',
        };
    }

    public function browser(): string
    {
        $agent = (string) $this->request->userAgent();

        return match (true) {
            str_contains($agent, 'Edg/') => 'Edge',
            str_contains($agent, 'OPR/') || str_contains($agent, 'Opera') => 'Opera',
            str_contains($agent, 'SamsungBrowser') => 'Samsung Internet',
            str_contains($agent, 'Firefox/') => 'Firefox',
            str_contains($agent, 'Chrome/') => 'Chrome',
            str_contains($agent, 'Safari/') => 'Safari',
            default => 'Autre',
        };
    }

    public function os(): string
    {
        $agent = (string) $this->request->userAgent();

        return match (true) {
            str_contains($agent, 'Windows') => 'Windows',
            str_contains($agent, 'Android') => 'Android',
            (bool) preg_match('/iPhone|iPad|iPod/', $agent) => 'iOS',
            str_contains($agent, 'Mac OS') => 'macOS',
            str_contains($agent, 'Linux') => 'Linux',
            default => 'Autre',
        };
    }

    public function referrerHost(?string $referrer): ?string
    {
        $host = $referrer ? parse_url($referrer, PHP_URL_HOST) : null;

        // Les navigations internes ne sont pas des sources de trafic.
        if (! $host || $host === $this->request->getHost()) {
            return null;
        }

        return mb_substr(preg_replace('/^www\./', '', strtolower($host)), 0, 120);
    }
}

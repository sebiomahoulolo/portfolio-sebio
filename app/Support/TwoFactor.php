<?php

namespace App\Support;

use App\Models\User;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * Double authentification par application (Google Authenticator, Microsoft
 * Authenticator, 2FAS, Aegis...) : codes TOTP à 6 chiffres renouvelés toutes les 30 s.
 */
class TwoFactor
{
    private const RECOVERY_CODES = 8;

    public function __construct(private Google2FA $google2fa = new Google2FA) {}

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey(32);
    }

    /**
     * QR code à scanner avec l'application, en SVG.
     */
    public function qrCodeSvg(User $user, string $secret): string
    {
        $url = $this->google2fa->getQRCodeUrl(config('app.name'), $user->email, $secret);

        $svg = (new Writer(new ImageRenderer(
            new RendererStyle(200, 1, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(17, 17, 17))),
            new SvgImageBackEnd,
        )))->writeString($url);

        return trim(substr($svg, strpos($svg, "\n") + 1)); // sans la déclaration XML
    }

    /**
     * Vérifie un code à 6 chiffres. Un code déjà utilisé est refusé (rejeu),
     * et une tolérance d'un intervalle absorbe un léger décalage d'horloge.
     */
    public function verify(string $secret, string $code, string $replayKey): bool
    {
        $code = preg_replace('/\D/', '', $code);
        if (strlen($code) !== 6) {
            return false;
        }

        $cacheKey = 'two-factor.last-timestamp.'.$replayKey;
        $timestamp = $this->google2fa->verifyKeyNewer($secret, $code, (int) Cache::get($cacheKey, 0), 1);

        if ($timestamp === false) {
            return false;
        }

        // Avec un horodatage de référence, la bibliothèque renvoie celui du code accepté.
        Cache::put($cacheKey, (int) $timestamp, now()->addMinutes(5));

        return true;
    }

    /**
     * @return list<string>
     */
    public function generateRecoveryCodes(): array
    {
        return collect(range(1, self::RECOVERY_CODES))
            ->map(fn () => Str::lower(Str::random(5).'-'.Str::random(5)))
            ->all();
    }

    /**
     * Utilise un code de secours : il est retiré de la liste s'il est valide.
     */
    public function useRecoveryCode(User $user, string $code): bool
    {
        $code = Str::lower(trim($code));
        $codes = $user->two_factor_recovery_codes ?? [];

        foreach ($codes as $index => $candidate) {
            if (hash_equals($candidate, $code)) {
                unset($codes[$index]);
                $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

                return true;
            }
        }

        return false;
    }
}

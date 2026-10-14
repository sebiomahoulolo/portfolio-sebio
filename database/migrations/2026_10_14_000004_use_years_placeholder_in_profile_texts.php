<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Remplace les années d'expérience écrites en dur dans les textes du profil
 * (« plus de 2 ans », « over 2 years ») par le marqueur {ans}, calculé
 * automatiquement depuis le parcours.
 */
return new class extends Migration
{
    private const FIELDS = ['headline', 'subtitle', 'tagline', 'intro', 'about', 'objective', 'current_position', 'featured_project', 'seo_title', 'seo_description'];

    private const PATTERNS = [
        '/\b(plus de|depuis plus de|depuis près de|près de|depuis) \d+\+? ans\b/u' => '$1 {ans} ans',
        '/\b\d+\+? ans d([’\']) ?expérience/u' => '{ans} ans d$1expérience',
        '/\b(over|more than|for over|for more than|nearly|almost) \d+\+? years\b/u' => '$1 {ans} years',
        '/\b\d+\+? years of experience\b/u' => '{ans} years of experience',
    ];

    public function up(): void
    {
        foreach (DB::table('profile_settings')->get() as $profile) {
            $changes = [];

            foreach (self::FIELDS as $field) {
                $values = json_decode((string) ($profile->{$field} ?? ''), true);
                if (! is_array($values)) {
                    continue;
                }

                $updated = array_map(
                    fn ($text) => is_string($text) ? preg_replace(array_keys(self::PATTERNS), array_values(self::PATTERNS), $text) : $text,
                    $values,
                );

                if ($updated !== $values) {
                    $changes[$field] = json_encode($updated, JSON_UNESCAPED_UNICODE);
                }
            }

            if ($changes) {
                DB::table('profile_settings')->where('id', $profile->id)->update($changes);
            }
        }
    }

    public function down(): void
    {
        // Le marqueur reste lisible : rien à annuler.
    }
};

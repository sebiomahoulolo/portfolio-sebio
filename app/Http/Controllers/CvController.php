<?php

namespace App\Http\Controllers;

use App\Models\ProfileSetting;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CvController extends Controller
{
    /**
     * Sert le CV téléversé depuis l'administration, ou à défaut l'ancien
     * fichier du site.
     */
    public function __invoke(): Response
    {
        $profile = ProfileSetting::current();
        $filename = 'CV-'.str($profile->name)->slug().'.pdf';
        $disk = Storage::disk(config('portfolio.media_disk'));

        if ($profile->cv_path && $disk->exists($profile->cv_path)) {
            return $disk->response($profile->cv_path, $filename, ['Content-Type' => 'application/pdf'], 'inline');
        }

        $legacy = public_path('cv/CV Resume SEBIO MAHOULOLO 2025.pdf');
        abort_unless(is_file($legacy), 404);

        return response()->file($legacy, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use App\Models\Education;
use App\Models\Experience;
use App\Models\ProfileSetting;
use App\Models\Project;
use App\Models\Service;
use App\Models\Technology;
use App\Models\Testimonial;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $profile = ProfileSetting::current();

        $projects = Project::published()
            ->with('technologies:id,name')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        $experiences = Experience::visible()->orderByRaw('ended_at IS NOT NULL')->orderByDesc('started_at')->get();
        $firstStart = Experience::visible()->min('started_at');

        return view('site.home', [
            'profile' => $profile,
            'projects' => $projects,
            'publishedCount' => Project::published()->count(),
            'services' => Service::published()->orderBy('sort_order')->get(),
            'testimonials' => Testimonial::published()->orderBy('sort_order')->get(),
            'skills' => Technology::where('show_in_skills', true)->orderBy('sort_order')->get()->groupBy('category'),
            'stack' => Technology::where('show_in_skills', true)->orderBy('category')->orderBy('sort_order')->get(['name', 'icon']),
            'experiences' => $experiences,
            'educations' => Education::visible()->orderBy('sort_order')->get(),
            'certifications' => Certification::visible()->orderByDesc('issued_at')->get(),
            'yearsOfExperience' => $profile->years_experience
                ?: ($firstStart ? max(1, (int) now()->diffInYears($firstStart, true)) : null),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use App\Models\Education;
use App\Models\Experience;
use App\Models\ProfileSetting;
use App\Models\Project;
use App\Models\Technology;
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
            'skills' => Technology::where('show_in_skills', true)->orderBy('sort_order')->get()->groupBy('category'),
            'marquee' => Technology::where('show_in_skills', true)->orderBy('sort_order')->pluck('name'),
            'experiences' => $experiences,
            'educations' => Education::visible()->orderBy('sort_order')->get(),
            'certifications' => Certification::visible()->orderByDesc('issued_at')->get(),
            'yearsOfExperience' => $firstStart ? max(1, (int) now()->diffInYears($firstStart, true)) : null,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ProfileSetting;
use App\Models\Project;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('site.services.index', [
            'profile' => ProfileSetting::current(),
            'services' => Service::published()->orderBy('sort_order')->get(),
            'testimonials' => Testimonial::published()->orderBy('sort_order')->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $service = Service::published()->where('slug', $slug)->firstOrFail();

        return view('site.services.show', [
            'profile' => ProfileSetting::current(),
            'service' => $service,
            'others' => Service::published()->whereKeyNot($service->id)->orderBy('sort_order')->get(),
            'projects' => Project::published()->with('technologies:id,name')->orderByDesc('is_featured')->orderBy('sort_order')->limit(3)->get(),
        ]);
    }
}

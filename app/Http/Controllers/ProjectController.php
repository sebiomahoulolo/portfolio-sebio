<?php

namespace App\Http\Controllers;

use App\Models\ProfileSetting;
use App\Models\Project;
use App\Models\Technology;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $technology = $request->filled('techno')
            ? Technology::where('slug', $request->string('techno'))->first()
            : null;

        $projects = Project::published()
            ->with('technologies:id,name')
            ->when($technology, fn ($q) => $q->whereHas('technologies', fn ($t) => $t->whereKey($technology->id)))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->get();

        // Seules les technologies utilisées par au moins un projet publié servent de filtre.
        $filters = Technology::whereHas('projects', fn ($q) => $q->published())
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return view('site.projects.index', [
            'profile' => ProfileSetting::current(),
            'projects' => $projects,
            'filters' => $filters,
            'activeTechnology' => $technology,
        ]);
    }

    public function show(Request $request, string $slug): View|RedirectResponse
    {
        // Les brouillons, contenus programmés et projets supprimés restent introuvables.
        $project = $this->findLocalized(Project::published()->with(['technologies', 'images']), $slug, 'projects.show');
        if ($project instanceof RedirectResponse) {
            return $project;
        }

        // Une seule consultation comptée par projet et par session.
        $viewed = $request->session()->get('viewed_projects', []);
        if (! in_array($project->id, $viewed, true)) {
            $project->increment('views_count');
            $request->session()->put('viewed_projects', [...$viewed, $project->id]);
        }

        $ordered = Project::published()->orderByDesc('is_featured')->orderBy('sort_order')->get(['id', 'slug', 'slug_en', 'title']);
        $position = $ordered->search(fn ($p) => $p->id === $project->id);

        return view('site.projects.show', [
            'profile' => ProfileSetting::current(),
            'project' => $project,
            'previous' => $position > 0 ? $ordered[$position - 1] : null,
            'next' => $ordered[$position + 1] ?? null,
        ]);
    }
}

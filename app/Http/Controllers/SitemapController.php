<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Seules les pages publiques, publiées et indexables y figurent.
     */
    public function __invoke(): Response
    {
        $projects = Project::published()->where('is_indexable', true)->orderBy('sort_order')->get(['slug', 'updated_at']);

        return response()
            ->view('site.sitemap', ['projects' => $projects, 'lastProjectUpdate' => $projects->max('updated_at')])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}

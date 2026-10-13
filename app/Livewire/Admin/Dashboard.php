<?php

namespace App\Livewire\Admin;

use App\Enums\MessageStatus;
use App\Enums\PublicationStatus;
use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Models\PageView;
use App\Models\Project;
use App\Models\Technology;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Tableau de bord')]
class Dashboard extends Component
{
    public function render()
    {
        $canSeeContacts = Gate::allows('manage-contacts');

        return view('livewire.admin.dashboard', [
            'publishedProjects' => Project::published()->count(),
            'draftProjects' => Project::where('status', PublicationStatus::Draft)->count(),
            'technologies' => Technology::count(),
            'projectViews' => (int) Project::sum('views_count'),
            'projectViewsDetail' => Project::orderByDesc('views_count')->orderBy('sort_order')->get(),
            'totalVisitors' => PageView::publicVisitorCount(),
            'visitorsToday' => PageView::where('created_at', '>=', now()->startOfDay())->distinct()->count('visitor_hash'),
            'onlineNow' => PageView::where('created_at', '>=', now()->subMinutes(5))->distinct()->count('session_hash'),
            'canSeeAnalytics' => Gate::allows('view-analytics'),
            'canSeeContacts' => $canSeeContacts,
            'newMessages' => $canSeeContacts ? ContactMessage::where('status', MessageStatus::New)->count() : 0,
            'latestMessages' => $canSeeContacts ? ContactMessage::latest()->limit(5)->get() : collect(),
            'recentProjects' => Project::latest('updated_at')->limit(5)->get(),
            'activity' => Gate::allows('view-activity') ? ActivityLog::with('user:id,name')->latest('created_at')->limit(8)->get() : collect(),
        ]);
    }
}

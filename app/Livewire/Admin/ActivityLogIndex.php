<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title("Journal d'activité")]
class ActivityLogIndex extends Component
{
    use WithPagination;

    public function mount(): void
    {
        Gate::authorize('view-activity');
    }

    public function render()
    {
        return view('livewire.admin.activity-log-index', [
            'logs' => ActivityLog::with('user:id,name')->latest('created_at')->latest('id')->paginate(30),
        ]);
    }
}

<?php

namespace App\Livewire\Admin\Services;

use App\Models\Service;
use App\Support\ActivityLogger;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Services')]
class ServiceIndex extends Component
{
    public function mount(): void
    {
        $this->authorize('viewAny', Service::class);
    }

    #[Computed]
    public function services()
    {
        return Service::orderBy('sort_order')->orderBy('id')->get();
    }

    public function togglePublished(int $id): void
    {
        $service = Service::findOrFail($id);
        $this->authorize('publish', $service);

        $service->update(['is_published' => ! $service->is_published]);
        ActivityLogger::log('service.'.($service->is_published ? 'published' : 'unpublished'), 'Service '.($service->is_published ? 'publié' : 'masqué').' : '.$service->translate('title'), $service);
        unset($this->services);
    }

    public function move(int $id, string $direction): void
    {
        $services = $this->services->values();
        $index = $services->search(fn ($s) => $s->id === $id);
        $swap = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index === false || ! isset($services[$swap])) {
            return;
        }

        $this->authorize('update', $services[$index]);

        $ordered = $services->all();
        [$ordered[$index], $ordered[$swap]] = [$ordered[$swap], $ordered[$index]];
        foreach (array_values($ordered) as $position => $service) {
            $service->update(['sort_order' => $position]);
        }
        unset($this->services);
    }

    public function delete(int $id): void
    {
        $service = Service::findOrFail($id);
        $this->authorize('delete', $service);

        ActivityLogger::log('service.deleted', 'Service supprimé : '.$service->translate('title'));
        $service->delete();

        Flux::toast('Service supprimé.', variant: 'success');
        unset($this->services);
    }

    public function render()
    {
        return view('livewire.admin.services.service-index');
    }
}

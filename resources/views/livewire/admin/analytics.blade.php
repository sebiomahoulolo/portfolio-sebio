@php
    use App\Livewire\Admin\Analytics;

    $s = $this->summary;
    $series = $this->series;
    $b = $this->breakdowns;
    $max = max(1, $series->max('views'));
    $deviceLabels = ['desktop' => 'Ordinateur', 'mobile' => 'Mobile', 'tablet' => 'Tablette'];
    $deviceIcons = ['desktop' => 'computer-desktop', 'mobile' => 'device-phone-mobile', 'tablet' => 'device-tablet'];
    $kpis = [
        ['users', 'Visiteurs uniques', number_format($s['visitors'], 0, ',', ' '), 'Empreinte anonyme renouvelée chaque jour'],
        ['cursor-arrow-rays', 'Visites', number_format($s['visits'], 0, ',', ' '), $s['pages_per_visit'].' page(s) par visite'],
        ['document-text', 'Pages vues', number_format($s['views'], 0, ',', ' '), 'Toutes pages publiques'],
        ['clock', 'Durée moyenne', Analytics::duration($s['avg_duration']), 'Temps actif par visite'],
        ['arrow-uturn-left', 'Taux de rebond', $s['bounce_rate'].' %', 'Visites d’une seule page'],
    ];
@endphp

<div class="flex flex-col gap-6" wire:poll.60s>
    <x-admin.page-header title="Statistiques" description="Audience du site public, sans cookie publicitaire ni donnée personnelle. Les visites des administrateurs ne sont pas comptées.">
        <x-slot:actions>
            <flux:badge color="green" icon="signal" size="lg">{{ $s['online'] }} en ligne</flux:badge>
            <flux:select wire:model.live="period" class="w-52">
                @foreach ($periods as $value => $label)
                    <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ($kpis as [$icon, $label, $value, $hint])
            <flux:card class="flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm">{{ $label }}</flux:text>
                    <flux:icon :name="$icon" class="size-5 text-copper-dark" />
                </div>
                <div class="text-2xl font-semibold tabular-nums text-ink dark:text-paper">{{ $value }}</div>
                <flux:text class="text-xs">{{ $hint }}</flux:text>
            </flux:card>
        @endforeach
    </div>

    {{-- Évolution --}}
    <flux:card class="flex flex-col gap-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:heading>Évolution de l'audience</flux:heading>
            <div class="flex items-center gap-4 text-xs text-zinc-500">
                <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-copper"></span> Pages vues</span>
                <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-ink dark:bg-paper"></span> Visiteurs</span>
            </div>
        </div>
        @if ($s['views'] === 0)
            <x-admin.empty-state icon="chart-bar" title="Aucune visite sur cette période" description="Les visites apparaissent ici dès que le site est consulté." />
        @else
            <div class="overflow-x-auto">
                <div class="flex h-56 min-w-full items-end gap-1" style="min-width: {{ $series->count() * 14 }}px">
                    @foreach ($series as $point)
                        <div class="group relative flex h-full flex-1 flex-col justify-end" title="{{ $point['label'] }} : {{ $point['views'] }} pages vues, {{ $point['visitors'] }} visiteurs">
                            <div class="relative w-full rounded-t bg-copper/80 transition group-hover:bg-copper" style="height: {{ round($point['views'] / $max * 100, 1) }}%">
                                <div class="absolute inset-x-[25%] bottom-0 rounded-t bg-ink dark:bg-paper" style="height: {{ $point['views'] ? round($point['visitors'] / $point['views'] * 100, 1) : 0 }}%"></div>
                            </div>
                            <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-2 hidden -translate-x-1/2 whitespace-nowrap rounded-lg bg-ink px-2.5 py-1.5 text-xs text-paper shadow-lg group-hover:block">
                                <strong>{{ $point['label'] }}</strong><br>{{ $point['views'] }} pages · {{ $point['visitors'] }} visiteurs
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 flex justify-between text-xs text-zinc-500">
                    <span>{{ $series->first()['label'] }}</span>
                    <span>{{ $series->last()['label'] }}</span>
                </div>
            </div>
        @endif
    </flux:card>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Pages --}}
        <flux:card class="flex flex-col gap-4 lg:col-span-2">
            <flux:heading>Pages les plus consultées</flux:heading>
            @if ($b['pages']->isEmpty())
                <flux:text>Aucune donnée.</flux:text>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Page</flux:table.column>
                        <flux:table.column align="end">Vues</flux:table.column>
                        <flux:table.column align="end">Temps moyen</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($b['pages'] as $page)
                            <flux:table.row :key="$page->path">
                                <flux:table.cell class="max-w-md">
                                    <div class="truncate font-medium text-ink dark:text-paper">{{ $page->title ?: $page->path }}</div>
                                    <a href="{{ url($page->path) }}" target="_blank" class="truncate text-xs text-zinc-500 hover:text-copper-dark">{{ $page->path }}</a>
                                </flux:table.cell>
                                <flux:table.cell align="end" class="tabular-nums">{{ $page->total }}</flux:table.cell>
                                <flux:table.cell align="end" class="tabular-nums">{{ $page->avg_duration ? Analytics::duration((int) $page->avg_duration) : '—' }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </flux:card>

        {{-- Pays --}}
        <flux:card class="flex flex-col gap-4">
            <div class="flex items-center gap-2"><flux:icon.globe-europe-africa class="size-5 text-copper-dark" /><flux:heading>Pays</flux:heading></div>
            @php $countryMax = max(1, $b['countries']->max('total')); @endphp
            @forelse ($b['countries'] as $row)
                <div class="flex items-center gap-3 text-sm">
                    <span class="w-9 shrink-0 rounded bg-zinc-100 py-0.5 text-center text-xs font-semibold dark:bg-zinc-700">{{ $row->label }}</span>
                    <div class="min-w-0 flex-1">
                        <div class="flex justify-between gap-2"><span class="truncate">{{ Analytics::countryName($row->label) }}</span><span class="tabular-nums text-zinc-500">{{ $row->total }}</span></div>
                        <div class="mt-1 h-1.5 rounded-full bg-zinc-100 dark:bg-zinc-700"><div class="h-full rounded-full bg-copper" style="width: {{ round($row->total / $countryMax * 100) }}%"></div></div>
                    </div>
                </div>
            @empty
                <flux:text>Aucune donnée.</flux:text>
            @endforelse
        </flux:card>

        {{-- Sources --}}
        <flux:card class="flex flex-col gap-4">
            <div class="flex items-center gap-2"><flux:icon.link class="size-5 text-copper-dark" /><flux:heading>Sources de trafic</flux:heading></div>
            @php $refMax = max(1, $b['referrers']->max('total')); @endphp
            @forelse ($b['referrers'] as $row)
                <div class="text-sm">
                    <div class="flex justify-between gap-2"><span class="truncate">{{ $row->label }}</span><span class="tabular-nums text-zinc-500">{{ $row->total }}</span></div>
                    <div class="mt-1 h-1.5 rounded-full bg-zinc-100 dark:bg-zinc-700"><div class="h-full rounded-full bg-ink dark:bg-paper" style="width: {{ round($row->total / $refMax * 100) }}%"></div></div>
                </div>
            @empty
                <flux:text>Uniquement des visites directes pour le moment.</flux:text>
            @endforelse
        </flux:card>

        {{-- Appareils et navigateurs --}}
        <flux:card class="flex flex-col gap-4">
            <div class="flex items-center gap-2"><flux:icon.device-phone-mobile class="size-5 text-copper-dark" /><flux:heading>Appareils</flux:heading></div>
            @php $deviceTotal = max(1, $b['devices']->sum('total')); @endphp
            <div class="grid grid-cols-3 gap-3">
                @foreach (['desktop', 'mobile', 'tablet'] as $device)
                    @php $count = (int) optional($b['devices']->firstWhere('label', $device))->total; @endphp
                    <div class="rounded-xl bg-zinc-50 p-3 text-center dark:bg-zinc-700/50">
                        <flux:icon :name="$deviceIcons[$device]" class="mx-auto size-6 text-copper-dark" />
                        <div class="mt-1 text-lg font-semibold tabular-nums">{{ round($count / $deviceTotal * 100) }} %</div>
                        <div class="text-xs text-zinc-500">{{ $deviceLabels[$device] }}</div>
                    </div>
                @endforeach
            </div>
        </flux:card>

        <flux:card class="flex flex-col gap-4">
            <div class="flex items-center gap-2"><flux:icon.window class="size-5 text-copper-dark" /><flux:heading>Navigateurs et langues</flux:heading></div>
            <div class="flex flex-wrap gap-2">
                @foreach ($b['browsers'] as $row)
                    <flux:badge>{{ $row->label }} · {{ $row->total }}</flux:badge>
                @endforeach
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ($b['locales'] as $row)
                    <flux:badge color="orange">{{ strtoupper($row->label) }} · {{ $row->total }} pages</flux:badge>
                @endforeach
            </div>
        </flux:card>
    </div>

    {{-- Dernières visites --}}
    <flux:card class="flex flex-col gap-4">
        <flux:heading>Dernières pages consultées</flux:heading>
        @if ($recent->isEmpty())
            <flux:text>Aucune visite sur cette période.</flux:text>
        @else
            <flux:table :paginate="$recent">
                <flux:table.columns>
                    <flux:table.column>Date</flux:table.column>
                    <flux:table.column>Page</flux:table.column>
                    <flux:table.column>Pays</flux:table.column>
                    <flux:table.column>Appareil</flux:table.column>
                    <flux:table.column>Provenance</flux:table.column>
                    <flux:table.column align="end">Durée</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($recent as $view)
                        <flux:table.row :key="$view->id">
                            <flux:table.cell class="whitespace-nowrap text-sm text-zinc-500">{{ $view->created_at->format('d/m/Y H:i') }}</flux:table.cell>
                            <flux:table.cell class="max-w-xs truncate">{{ $view->path }}</flux:table.cell>
                            <flux:table.cell>{{ Analytics::countryName($view->country_code) }}</flux:table.cell>
                            <flux:table.cell>
                                <span class="inline-flex items-center gap-1.5 text-sm">
                                    <flux:icon :name="$deviceIcons[$view->device] ?? 'computer-desktop'" variant="micro" class="text-zinc-400" />
                                    {{ $view->browser }} · {{ $view->os }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell class="text-sm text-zinc-500">{{ $view->referrer_host ?? 'Direct' }}</flux:table.cell>
                            <flux:table.cell align="end" class="tabular-nums">{{ $view->duration_seconds ? Analytics::duration($view->duration_seconds) : '—' }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

    <flux:text class="text-xs">Total public affiché sur le site : {{ number_format($s['total_visitors'], 0, ',', ' ') }} visiteurs depuis le lancement.</flux:text>
</div>

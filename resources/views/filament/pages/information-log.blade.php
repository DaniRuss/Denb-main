<x-filament-panels::page>

    {{-- Header Widgets: Stat Cards --}}
    @if ($this->getHeaderWidgets())
        <x-filament-widgets::widgets
            :widgets="$this->getHeaderWidgets()"
            :columns="$this->getHeaderWidgetsColumns()"
        />
    @endif

    {{-- Footer Widgets: Data Tables in two-column grid --}}
    @if ($this->getFooterWidgets())
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-6">
            @foreach ($this->getFooterWidgets() as $widget)
                <div class="col-span-1">
                    @livewire($widget)
                </div>
            @endforeach
        </div>
    @endif

</x-filament-panels::page>

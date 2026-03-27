{{-- resources/views/filament/widgets/quick-actions.blade.php --}}
<x-filament-widgets::widget>
    <x-filament::section>
        <div class="p-4">
            <h2 class="text-lg font-bold mb-4">Quick Actions</h2>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 border border-gray-100 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-primary-700">{{ $this->getPendingCount() }}</div>
                    <div class="text-sm text-gray-600">Pending Complaints</div>
                    <a href="{{ \App\Filament\Resources\ComplaintResource::getUrl('index', ['tableFilters[status][value]' => 'pending']) }}"
                        class="text-xs text-primary-600 mt-2 inline-block font-medium hover:underline">
                        View All →
                    </a>
                </div>

                <div class="bg-red-50 border border-red-100 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-danger-700">{{ $this->getUrgentCount() }}</div>
                    <div class="text-sm text-gray-600">Urgent Cases</div>
                    <a href="{{ \App\Filament\Resources\ComplaintResource::getUrl('index', ['tableFilters[priority][value]' => 'high']) }}"
                        class="text-xs text-danger-600 mt-2 inline-block font-medium hover:underline">
                        View All →
                    </a>
                </div>

                <div class="bg-amber-50 border border-amber-100 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-warning-700">{{ $this->getUnassignedTipsCount() }}</div>
                    <div class="text-sm text-gray-600">Unassigned Tips</div>
                    <a href="{{ \App\Filament\Resources\TipResource::getUrl('index', ['tableFilters[unassigned][value]' => true]) }}"
                        class="text-xs text-warning-600 mt-2 inline-block font-medium hover:underline">
                        View All →
                    </a>
                </div>

                <div class="bg-red-50 border border-red-100 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-danger-700">{{ $this->getImmediateTipsCount() }}</div>
                    <div class="text-sm text-gray-600">Immediate Action Tips</div>
                    <a href="{{ \App\Filament\Resources\TipResource::getUrl('index', ['tableFilters[urgency_level][value]' => 'immediate']) }}"
                        class="text-xs text-danger-600 mt-2 inline-block font-medium hover:underline">
                        View All →
                    </a>
                </div>
            </div>

            <div class="mt-4 flex gap-2">
                <a href="{{ \App\Filament\Resources\ComplaintResource::getUrl('index') }}"
                    class="flex-1 bg-gray-100 text-center py-2 rounded text-sm font-medium hover:bg-gray-200 transition-colors">
                    All Complaints
                </a>
                <a href="{{ \App\Filament\Resources\TipResource::getUrl('index') }}"
                    class="flex-1 bg-gray-100 text-center py-2 rounded text-sm font-medium hover:bg-gray-200 transition-colors">
                    All Tips
                </a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
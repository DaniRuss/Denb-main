<div class="space-y-6 p-4">
    <div class="border-b pb-4">
        <h3 class="text-lg font-bold">Asset Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 text-sm">
            <div><span class="font-semibold">Asset Type:</span> {{ $record->asset_type }}</div>
            <div><span class="font-semibold">Status:</span> {{ $record->status }}</div>
            <div><span class="font-semibold">Owner Name:</span> {{ $record->owner_name ?? 'N/A' }}</div>
            <div><span class="font-semibold">Owner Phone:</span> {{ $record->owner_phone ?? 'N/A' }}</div>
            <div><span class="font-semibold">Date Confiscated:</span> {{ $record->date_confiscated->format('Y-m-d') }}</div>
            <div><span class="font-semibold">Registered By:</span> Officer {{ $record->officer->badge_number ?? 'Unknown' }}</div>
            <div class="col-span-full"><span class="font-semibold">Description:</span> {{ $record->description }}</div>
            <div class="col-span-full"><span class="font-semibold">Location:</span> 
                {{ $record->subCity->name_en ?? $record->subCity->name_am ?? 'N/A' }} Sub-City, 
                {{ $record->woreda->name_en ?? $record->woreda->name_am ?? 'N/A' }} Woreda 
                (Kebele: {{ $record->kebele ?? 'N/A' }}, House: {{ $record->house_number ?? 'N/A' }})
            </div>
        </div>
    </div>

    <div>
        <h3 class="text-lg font-bold mb-4">Lifecycle History</h3>
        @if($record->activities->isEmpty())
            <p class="text-gray-500 italic">No recorded history for this asset.</p>
        @else
            <div class="space-y-4">
                @foreach($record->activities->sortByDesc('created_at') as $activity)
                    <div class="relative pl-6 pb-4 border-l-2 border-primary-500 last:border-0 last:pb-0">
                        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-primary-500"></div>
                        <div class="text-xs text-gray-500 mb-1">
                            {{ $activity->created_at->format('Y-m-d H:i') }} • 
                            By: {{ optional($activity->user)->name ?? 'System' }}
                        </div>
                        <div class="font-bold text-sm">{{ $activity->action }}</div>
                        <div class="text-sm text-gray-700 mt-1">{{ $activity->description }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

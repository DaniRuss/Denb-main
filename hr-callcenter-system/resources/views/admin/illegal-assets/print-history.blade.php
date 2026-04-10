<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Asset History - {{ $asset->asset_type }}</title>
    <style>
        body { font-family: sans-serif; margin: 2rem; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #ccc; padding-bottom: 1rem; margin-bottom: 2rem; }
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem; }
        .property { margin-bottom: 0.5rem; }
        .property strong { font-weight: bold; }
        .history-list { list-style-type: none; padding: 0; }
        .history-item { padding: 1rem; border-left: 4px solid #3b82f6; background-color: #f9fafb; margin-bottom: 1rem; }
        .history-date { font-size: 0.85rem; color: #6b7280; margin-bottom: 0.25rem; }
        .history-action { font-weight: bold; font-size: 1.1rem; margin-bottom: 0.5rem; }
        .history-desc { margin: 0; }
        @media print {
            body { margin: 0; padding: 1rem; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Illegal Asset History Report</h1>
        <p>Generated on {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <h2>Asset Details</h2>
    <div class="details-grid">
        <div class="property"><strong>Asset Type:</strong> {{ $asset->asset_type }}</div>
        <div class="property"><strong>Status:</strong> {{ $asset->status }}</div>
        <div class="property"><strong>Owner Name:</strong> {{ $asset->owner_name ?? 'N/A' }}</div>
        <div class="property"><strong>Owner Phone:</strong> {{ $asset->owner_phone ?? 'N/A' }}</div>
        <div class="property"><strong>Date Confiscated:</strong> {{ $asset->date_confiscated->format('Y-m-d') }}</div>
        <div class="property"><strong>Registered By:</strong> Officer {{ $asset->officer->badge_number ?? 'Unknown' }}</div>
        <div class="property" style="grid-column: span 2;"><strong>Description:</strong> {{ $asset->description }}</div>
        <div class="property" style="grid-column: span 2;"><strong>Location Details:</strong> 
            {{ $asset->subCity->name_en ?? $asset->subCity->name_am ?? 'N/A' }} Sub-City, 
            {{ $asset->woreda->name_en ?? $asset->woreda->name_am ?? 'N/A' }} Woreda 
            (Kebele: {{ $asset->kebele ?? 'N/A' }}, House: {{ $asset->house_number ?? 'N/A' }})
        </div>
    </div>

    <h2>Lifecycle History</h2>
    @if($asset->activities->isEmpty())
        <p>No recorded history for this asset.</p>
    @else
        <ul class="history-list">
            @foreach($asset->activities->sortByDesc('created_at') as $activity)
                <li class="history-item">
                    <div class="history-date">{{ $activity->created_at->format('Y-m-d H:i') }} - App User: {{ optional($activity->user)->name ?? 'System' }}</div>
                    <div class="history-action">{{ $activity->action }}</div>
                    <p class="history-desc">{{ $activity->description }}</p>
                </li>
            @endforeach
        </ul>
    @endif

    <div class="no-print" style="margin-top: 2rem; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #3b82f6; color: white; border: none; border-radius: 4px;">Print / Save as PDF</button>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>

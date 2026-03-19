<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDisposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'illegal_asset_id',
        'disposal_method',
        'disposal_date',
        'disposed_by_officer_id',
        'notes',
    ];

    protected $casts = [
        'disposal_date' => 'date',
    ];

    public function illegalAsset(): BelongsTo
    {
        return $this->belongsTo(IllegalAsset::class);
    }

    public function disposedByOfficer(): BelongsTo
    {
        return $this->belongsTo(Officer::class, 'disposed_by_officer_id');
    }
}

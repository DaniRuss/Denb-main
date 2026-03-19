<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetEstimation extends Model
{
    use HasFactory;

    protected $fillable = [
        'illegal_asset_id',
        'estimated_value',
        'evaluator_name',
        'evaluation_date',
        'notes',
    ];

    protected $casts = [
        'evaluation_date' => 'date',
        'estimated_value' => 'decimal:2',
    ];

    public function illegalAsset(): BelongsTo
    {
        return $this->belongsTo(IllegalAsset::class);
    }
}

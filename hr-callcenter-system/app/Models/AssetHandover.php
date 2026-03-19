<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetHandover extends Model
{
    use HasFactory;

    protected $fillable = [
        'illegal_asset_id',
        'department_id',
        'handed_over_to_officer_id',
        'handover_date',
        'notes',
    ];

    protected $casts = [
        'handover_date' => 'date',
    ];

    public function illegalAsset(): BelongsTo
    {
        return $this->belongsTo(IllegalAsset::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function handedOverToOfficer(): BelongsTo
    {
        return $this->belongsTo(Officer::class, 'handed_over_to_officer_id');
    }
}

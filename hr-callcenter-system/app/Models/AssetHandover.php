<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetHandover extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    protected $casts = [
        'handover_date' => 'date',
        'confirmed_at' => 'datetime',
    ];

    public function illegalAsset(): BelongsTo
    {
        return $this->belongsTo(IllegalAsset::class);
    }

    public function toWoreda(): BelongsTo
    {
        return $this->belongsTo(Woreda::class, 'to_woreda_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function handedOverToOfficer(): BelongsTo
    {
        return $this->belongsTo(Officer::class, 'handed_over_to_officer_id');
    }

    public function confirmedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by_user_id');
    }
}

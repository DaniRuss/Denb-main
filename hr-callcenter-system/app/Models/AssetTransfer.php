<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'illegal_asset_id',
        'from_department_id',
        'to_department_id',
        'from_storage_facility',
        'to_storage_facility',
        'transfer_date',
        'transferred_by_officer_id',
        'notes',
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    public function illegalAsset(): BelongsTo
    {
        return $this->belongsTo(IllegalAsset::class);
    }

    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    public function transferredByOfficer(): BelongsTo
    {
        return $this->belongsTo(Officer::class, 'transferred_by_officer_id');
    }
}

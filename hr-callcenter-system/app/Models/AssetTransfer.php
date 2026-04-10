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
        'from_woreda_id',
        'to_sub_city_id',
        'from_department_id',
        'to_department_id',
        'from_storage_facility',
        'to_storage_facility',
        'transferred_by_officer_id',
        'transfer_date',
        'notes',
        'confirmation_status',
        'confirmed_by_user_id',
        'confirmed_at',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'confirmed_at' => 'datetime',
    ];

    public function illegalAsset(): BelongsTo
    {
        return $this->belongsTo(IllegalAsset::class);
    }

    public function fromWoreda(): BelongsTo
    {
        return $this->belongsTo(Woreda::class, 'from_woreda_id');
    }

    public function toSubCity(): BelongsTo
    {
        return $this->belongsTo(SubCity::class, 'to_sub_city_id');
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

    public function confirmedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by_user_id');
    }
}

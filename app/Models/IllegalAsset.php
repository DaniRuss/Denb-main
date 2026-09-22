<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IllegalAsset extends Model
{
    use HasFactory;

    public const STATUS_REGISTERED = 'Registered';
    public const STATUS_HANDOVER_PENDING = 'Handover Pending';
    public const STATUS_HANDED_OVER = 'Handed Over to Woreda';
    public const STATUS_HANDOVER_REJECTED = 'Handover Rejected';
    public const STATUS_TRANSFER_PENDING = 'Transfer Pending';
    public const STATUS_TRANSFERRED = 'Transferred to Sub-City';
    public const STATUS_TRANSFER_REJECTED = 'Transfer Rejected';

    protected $fillable = [
        'asset_type',
        'quantity',
        'description',
        'owner_name',
        'owner_phone',
        'sub_city_id',
        'woreda_id',
        'kebele',
        'house_number',
        'location_found',
        'date_confiscated',
        'officer_id',
        'status',
    ];

    protected $casts = [
        'date_confiscated' => 'date',
    ];

    public function officer(): BelongsTo
    {
        return $this->belongsTo(Officer::class);
    }

    public function subCity(): BelongsTo
    {
        return $this->belongsTo(SubCity::class);
    }

    public function woreda(): BelongsTo
    {
        return $this->belongsTo(Woreda::class);
    }

    public function handovers(): HasMany
    {
        return $this->hasMany(AssetHandover::class);
    }

    public function estimations(): HasMany
    {
        return $this->hasMany(AssetEstimation::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(AssetTransfer::class);
    }

    public function sale(): HasOne
    {
        return $this->hasOne(AssetSale::class);
    }

    public function disposal(): HasOne
    {
        return $this->hasOne(AssetDisposal::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(AssetActivity::class, 'illegal_asset_id');
    }
}

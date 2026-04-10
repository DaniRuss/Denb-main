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

    protected $fillable = [
        'asset_type',
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
        'department_id',
        'status',
    ];

    protected $casts = [
        'date_confiscated' => 'date',
    ];

    public function officer(): BelongsTo
    {
        return $this->belongsTo(Officer::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
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

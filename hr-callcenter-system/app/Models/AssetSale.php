<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'illegal_asset_id',
        'buyer_name',
        'buyer_contact',
        'sale_price',
        'sale_date',
        'sold_by_officer_id',
        'notes',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'sale_price' => 'decimal:2',
    ];

    public function illegalAsset(): BelongsTo
    {
        return $this->belongsTo(IllegalAsset::class);
    }

    public function soldByOfficer(): BelongsTo
    {
        return $this->belongsTo(Officer::class, 'sold_by_officer_id');
    }
}

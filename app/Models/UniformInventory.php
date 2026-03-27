<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UniformInventory extends Model
{
    use HasFactory;

    protected $table = 'uniform_inventories';

    protected $fillable = [
        'item_name',
        'item_name_am',
        'category',
        'size',
        'quantity_in_stock',
        'min_stock_level',
        'unit',
        'location',
        'supplier',
        'unit_cost',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'unit_cost' => 'decimal:2',
    ];

    public function isLowStock(): bool
    {
        return $this->quantity_in_stock <= $this->min_stock_level;
    }

    public function distributions()
    {
        return $this->hasMany(UniformDistribution::class);
    }
}

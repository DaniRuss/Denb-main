<?php

namespace App\Models;

use App\Support\EthiopianTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Shift $shift): void {
            if (filled($shift->start_eth)) {
                $shift->start_eth = EthiopianTime::normalizeEthHm((string) $shift->start_eth);
            }
            if (filled($shift->end_eth)) {
                $shift->end_eth = EthiopianTime::normalizeEthHm((string) $shift->end_eth);
            }
        });
    }

    protected $fillable = [
        'name',
        'start_eth',
        'start_cycle',
        'end_eth',
        'end_cycle',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_cycle' => 'integer',
        'end_cycle' => 'integer',
    ];

    public function shiftAssignments()
    {
        return $this->hasMany(ShiftAssignment::class);
    }
}

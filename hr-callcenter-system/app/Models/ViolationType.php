<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViolationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'penalty_schedule_id',
        'code',
        'name_am',
        'name_en',
        'description',
        'regulation_reference',
        'fine_amount',
        'min_fine',
        'max_fine',
        'is_active',
    ];

    protected $casts = [
        'fine_amount' => 'decimal:2',
        'min_fine' => 'decimal:2',
        'max_fine' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function penaltySchedule(): BelongsTo
    {
        return $this->belongsTo(PenaltySchedule::class);
    }

    public function getDisplayNameAttribute(): string
    {
        $code = $this->code ? "[{$this->code}] " : '';
        return $code . $this->name_am;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

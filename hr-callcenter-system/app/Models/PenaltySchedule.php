<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenaltySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_am',
        'name_en',
        'level',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer',
    ];

    public function violationTypes(): HasMany
    {
        return $this->hasMany(ViolationType::class);
    }

    public function activeViolationTypes(): HasMany
    {
        return $this->hasMany(ViolationType::class)->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('level');
    }
}

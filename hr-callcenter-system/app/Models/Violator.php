<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Violator extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'full_name_am',
        'full_name_en',
        'sub_city_id',
        'woreda_id',
        'specific_location',
        'house_number',
        'phone',
        'id_number',
        'notes',
    ];

    // ── Relationships ──────────────────────────────

    public function subCity(): BelongsTo
    {
        return $this->belongsTo(SubCity::class);
    }

    public function woreda(): BelongsTo
    {
        return $this->belongsTo(Woreda::class);
    }

    public function violationRecords(): HasMany
    {
        return $this->hasMany(ViolationRecord::class);
    }

    // ── Accessors ���─────────────────────────────────

    public function getDisplayNameAttribute(): string
    {
        return $this->full_name_am . ($this->full_name_en ? " ({$this->full_name_en})" : '');
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->subCity?->name_am,
            $this->woreda?->name_am,
            $this->specific_location,
        ]);

        return implode(', ', $parts);
    }

    // ── Scopes ───���─────────────────────────────────

    public function scopeIndividuals($query)
    {
        return $query->where('type', 'individual');
    }

    public function scopeOrganizations($query)
    {
        return $query->where('type', 'organization');
    }

    public function scopeInSubCity($query, int $subCityId)
    {
        return $query->where('sub_city_id', $subCityId);
    }

    public function scopeInWoreda($query, int $woredaId)
    {
        return $query->where('woreda_id', $woredaId);
    }
}

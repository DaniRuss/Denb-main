<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'campaign_code', 'name_am', 'name_en', 'description_am',
        'description_en', 'category', 'sub_city_id', 'woreda_id', 'block',
        'start_date', 'end_date', 'target_audience', 'specific_place', 'status', 'created_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($m) => $m->campaign_code = 'CAMP-' . date('Ymd') . '-' . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT));
    }

    public function engagements()
    {
        return $this->hasMany(AwarenessEngagement::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subCity()
    {
        return $this->belongsTo(SubCity::class);
    }

    public function woreda()
    {
        return $this->belongsTo(Woreda::class);
    }

    // Scope: only active campaigns (used by Field Officer form dropdown)
    public function scopeActive($q)
    {
        return $q->where('status', 'active')
                 ->whereDate('start_date', '<=', today())
                 ->whereDate('end_date', '>=', today());
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VolunteerTip extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tip_code', 'engagement_id', 'suspect_name', 'violation_type', 'violation_location',
        'sub_city_id', 'woreda_id', 'block_number', 'violation_date', 'reported_date',
        'volunteer_name', 'is_anonymous', 'volunteer_signature_path', 'received_by', 'status',
        'verified_by', 'verified_at', 'investigated_by', 'action_taken', 'action_notes', 'action_date'
    ];

    protected $casts = [
        'violation_date' => 'date',
        'reported_date' => 'date',
        'verified_at' => 'timestamp',
        'action_date' => 'date',
        'is_anonymous' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($m) => $m->tip_code = 'TIP-' . date('Ymd') . '-' . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT));
    }

    public function engagement()
    {
        return $this->belongsTo(AwarenessEngagement::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function investigatedBy()
    {
        return $this->belongsTo(User::class, 'investigated_by');
    }

    public function assets()
    {
        return $this->hasMany(ConfiscatedAsset::class, 'volunteer_tip_id');
    }

    public function subCity()
    {
        return $this->belongsTo(SubCity::class);
    }

    public function woreda()
    {
        return $this->belongsTo(Woreda::class);
    }


    public function scopeVerified($q)
    {
        return $q->where('status', 'verified');
    }

    public function scopeForOfficer($q)
    {
        return $q->whereIn('status', ['verified', 'investigating', 'action_taken']);
    }
}

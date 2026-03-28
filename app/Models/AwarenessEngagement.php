<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AwarenessEngagement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'engagement_code', 'campaign_id', 'engagement_type', 'sub_city_id', 'woreda_id', 'block_number',
        'violation_type', 'round_number', 'citizen_name', 'citizen_gender', 'citizen_age',
        'headcount', 'stakeholder_partner', 'organization_type', 'org_headcount_male',
        'org_headcount_female', 'session_datetime', 'created_by', 'status', 'approved_by',
        'approved_at', 'rejection_note',
        // Media
        'violation_photo_path', 'officer_signature',
    ];

    protected $casts = [
        'session_datetime'   => 'datetime',
        'approved_at'        => 'timestamp',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($m) => $m->engagement_code = 'ENG-' . date('Ymd') . '-' . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT));
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function attendees()
    {
        return $this->hasMany(EngagementAttendee::class, 'engagement_id');
    }

    public function volunteerTips()
    {
        return $this->hasMany(VolunteerTip::class, 'engagement_id');
    }

    public function subCity()
    {
        return $this->belongsTo(SubCity::class);
    }

    public function woreda()
    {
        return $this->belongsTo(Woreda::class);
    }

    // Block-level scope — Paramilitary sees only their woreda
    public function scopeForUser($q, User $user)
    {
        if ($user->hasRole('paramilitary')) {
            return $q->where('created_by', $user->id);
        }
        if ($user->hasRole('woreda_coordinator')) {
            return $q->where('woreda_id', $user->woreda_id);
        }
        return $q; // Admin / Officer sees all
    }

    public function scopePendingApproval($q)
    {
        return $q->where('status', 'submitted');
    }

    // Violation type label map
    public static function violationLabels(): array
    {
        return [
            'illegal_land_invasion'  => 'Illegal Land Invasion',
            'illegal_construction'   => 'Illegal Construction',
            'illegal_expansion'      => 'Illegal Expansion',
            'illegal_waste_disposal' => 'Illegal Waste Disposal',
            'road_safety'            => 'Road Safety',
            'illegal_trade'          => 'Illegal Trade',
            'illegal_animal_trade'   => 'Illegal Animal Trade',
            'disturbing_acts'        => 'Disturbing Acts',
            'illegal_advertisement'  => 'Illegal Advertisement',
        ];
    }
}

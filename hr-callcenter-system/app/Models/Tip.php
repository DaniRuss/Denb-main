<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tip extends Model
{
    use HasFactory, SoftDeletes;

    public const SOURCE_PUBLIC = 'public';
    public const SOURCE_CALL_CENTER = 'call_center';

    public const STATUS_PENDING = 'pending';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_INVESTIGATING = 'investigating';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_ACTION_TAKEN = 'action_taken';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_FALSE_REPORT = 'false_report';

    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_PENDING_SUPERVISOR_REVIEW = 'pending_supervisor_review';
    public const STATUS_PENDING_DIRECTOR_REVIEW = 'pending_director_review';
    public const STATUS_DISPATCHED = 'dispatched';
    public const STATUS_UNDER_INVESTIGATION = 'under_investigation';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ESCALATED_TO_SUB_CITY = 'escalated_to_sub_city';

    protected $fillable = [
        'tip_number',
        'title',
        'tip_source',
        'reporter_name',
        'reporter_email',
        'reporter_phone',
        'caller_name',
        'caller_phone',
        'is_anonymous',
        'tip_type',
        'tip_type_other',
        'location',
        'sub_city',
        'woreda',
        'specific_address',
        'description',
        'suspect_name',
        'suspect_description',
        'suspect_vehicle',
        'suspect_company',
        'evidence_files',
        'has_evidence',
        'evidence_description',
        'urgency_level',
        'is_ongoing',
        'status',
        'created_by',
        'supervisor_comment',
        'director_comment',
        'investigation_status',
        'sub_city_notes',
        'supervisor_reviewed_at',
        'director_reviewed_at',
        'dispatched_at',
        'closed_at',
        'assigned_to',
        'assigned_department',
        'eligible_for_reward',
        'reward_amount',
        'reward_claimed',
        'access_token',
        'last_accessed',
        'unique_place',
        'dispatch_to',
    ];

    protected $casts = [
        'evidence_files' => 'array',
        'has_evidence' => 'boolean',
        'is_ongoing' => 'boolean',
        'is_anonymous' => 'boolean',
        'eligible_for_reward' => 'boolean',
        'reward_claimed' => 'boolean',
        'reward_amount' => 'decimal:2',
        'last_accessed' => 'datetime',
        'supervisor_reviewed_at' => 'datetime',
        'director_reviewed_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Tip $tip): void {
            $tip->tip_number = 'TIP-' . date('Ymd') . '-' . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            if ($tip->is_anonymous) {
                $tip->access_token = Str::random(32);
            }

            if (blank($tip->tip_source)) {
                $tip->tip_source = self::SOURCE_PUBLIC;
            }
        });
    }

    public static function getAddisAbabaSubCities(): array
    {
        return [
            'Addis Ketema' => 'Addis Ketema',
            'Akaky Kaliti' => 'Akaky Kaliti',
            'Arada' => 'Arada',
            'Bole' => 'Bole',
            'Gulele' => 'Gulele',
            'Kirkos' => 'Kirkos',
            'Kolfe Keranio' => 'Kolfe Keranio',
            'Lideta' => 'Lideta',
            'Nifas Silk-Lafto' => 'Nifas Silk-Lafto',
            'Yeka' => 'Yeka',
            'Lemi Kura' => 'Lemi Kura',
        ];
    }

    public static function getTipTypeOptions(): array
    {
        return [
            'illegal_trade' => 'Illegal Trade',
            'alcohol_sales' => 'Illegal Alcohol Sales',
            'land_grabbing' => 'Land Grabbing',
            'drug_activity' => 'Drug Activity',
            'counterfeit_goods' => 'Counterfeit Goods',
            'illegal_construction' => 'Illegal Construction',
            'environmental_violation' => 'Environmental Violation',
            'other' => 'Other',
        ];
    }

    public static function getUrgencyOptions(): array
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'immediate' => 'Immediate',
        ];
    }

    public static function getWoredaOptions(): array
    {
        $options = [];

        foreach (range(1, 15) as $number) {
            $options[(string) $number] = 'Woreda ' . $number;
        }

        return $options;
    }

    public static function getStatusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_INVESTIGATING => 'Investigating',
            self::STATUS_VERIFIED => 'Verified',
            self::STATUS_ACTION_TAKEN => 'Action Taken',
            self::STATUS_CLOSED => 'Closed',
            self::STATUS_FALSE_REPORT => 'False Report',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_PENDING_SUPERVISOR_REVIEW => 'Pending Supervisor Review',
            self::STATUS_PENDING_DIRECTOR_REVIEW => 'Pending Director Review',
            self::STATUS_DISPATCHED => 'Dispatched',
            self::STATUS_UNDER_INVESTIGATION => 'Under Investigation',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_ESCALATED_TO_SUB_CITY => 'Escalated to Sub-City',
        ];
    }

    public static function getStatusColors(): array
    {
        return [
            self::STATUS_PENDING => 'secondary',
            self::STATUS_UNDER_REVIEW => 'info',
            self::STATUS_INVESTIGATING => 'warning',
            self::STATUS_VERIFIED => 'success',
            self::STATUS_ACTION_TAKEN => 'success',
            self::STATUS_CLOSED => 'gray',
            self::STATUS_FALSE_REPORT => 'danger',
            self::STATUS_SUBMITTED => 'secondary',
            self::STATUS_PENDING_SUPERVISOR_REVIEW => 'warning',
            self::STATUS_PENDING_DIRECTOR_REVIEW => 'info',
            self::STATUS_DISPATCHED => 'success',
            self::STATUS_UNDER_INVESTIGATION => 'warning',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_ESCALATED_TO_SUB_CITY => 'danger',
        ];
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedDepartment()
    {
        return $this->belongsTo(Department::class, 'assigned_department');
    }

    public function updates()
    {
        return $this->morphMany(CaseUpdate::class, 'caseable');
    }

    public function assignments()
    {
        return $this->morphMany(CaseAssignment::class, 'caseable');
    }

    public function escalations()
    {
        return $this->morphMany(Escalation::class, 'caseable');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeImmediate(Builder $query): Builder
    {
        return $query->where('urgency_level', 'immediate');
    }

    public function scopeOngoing(Builder $query): Builder
    {
        return $query->where('is_ongoing', true);
    }

    public function scopeByLocation(Builder $query, string $location): Builder
    {
        return $query->where('location', 'LIKE', '%' . $location . '%');
    }

    public function scopeCallCenter(Builder $query): Builder
    {
        return $query->where('tip_source', self::SOURCE_CALL_CENTER);
    }

    public function getStatusNameAttribute(): string
    {
        return self::getStatusLabels()[$this->status] ?? str($this->status)->replace('_', ' ')->title()->toString();
    }

    public function getTipTypeNameAttribute(): string
    {
        $types = [
            'illegal_trade' => 'Illegal Trade',
            'alcohol_sales' => 'Alcohol Sales',
            'land_grabbing' => 'Land Grabbing',
            'drug_activity' => 'Drug Activity',
            'counterfeit_goods' => 'Counterfeit Goods',
            'illegal_construction' => 'Illegal Construction',
            'environmental_violation' => 'Environmental Violation',
            'other' => 'Other',
        ];

        return $types[$this->tip_type] ?? (string) $this->tip_type;
    }

    public function getUrgencyNameAttribute(): string
    {
        $urgencies = [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'immediate' => 'Immediate',
        ];

        return $urgencies[$this->urgency_level] ?? (string) $this->urgency_level;
    }
}

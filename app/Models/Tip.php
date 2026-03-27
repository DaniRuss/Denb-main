<?php
// app/Models/Tip.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tip extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tip_number',
        'reporter_name',
        'reporter_email',
        'reporter_phone',
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
        'assigned_to',
        'assigned_department',
        'eligible_for_reward',
        'reward_amount',
        'reward_claimed',
        'access_token',
        'last_accessed'
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
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tip) {
            // Generate unique tip number: TIP-20240306-123456
            $tip->tip_number = 'TIP-' . date('Ymd') . '-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Generate access token for anonymous tracking
            if ($tip->is_anonymous) {
                $tip->access_token = Str::random(32);
            }
        });
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
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

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeImmediate($query)
    {
        return $query->where('urgency_level', 'immediate');
    }

    public function scopeOngoing($query)
    {
        return $query->where('is_ongoing', true);
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'LIKE', "%{$location}%");
    }

    // Accessors
    public function getStatusNameAttribute()
    {
        $statuses = [
            'pending' => 'በመጠባበቅ ላይ',
            'under_review' => 'በግምገማ ላይ',
            'investigating' => 'ምርመራ በሂደት ላይ',
            'verified' => 'ተረጋግጧል',
            'action_taken' => 'እርምጃ ተወስዷል',
            'closed' => 'ተዘግቷል',
            'false_report' => 'የሐሰት ሪፖርት'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getTipTypeNameAttribute()
    {
        $types = [
            'illegal_trade' => 'ህገ-ወጥ ንግድ',
            'alcohol_sales' => 'ህገ-ወጥ አልኮል ሽያጭ',
            'land_grabbing' => 'የመሬት ወረራ',
            'drug_activity' => 'የአደንዛዥ እፅ ንግድ',
            'counterfeit_goods' => 'የሐሰት እቃዎች',
            'illegal_construction' => 'ህገ-ወጥ ግንባታ',
            'environmental_violation' => 'የአካባቢ ጥሰት',
            'other' => 'ሌላ'
        ];

        return $types[$this->tip_type] ?? $this->tip_type;
    }

    public function getUrgencyNameAttribute()
    {
        $urgencies = [
            'low' => 'ዝቅተኛ',
            'medium' => 'መካከለኛ',
            'high' => 'ከፍተኛ',
            'immediate' => 'አፋጣኝ'
        ];

        return $urgencies[$this->urgency_level] ?? $this->urgency_level;
    }
}

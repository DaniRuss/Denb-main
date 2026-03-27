<?php
// app/Models/Complaint.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'full_name',
        'email',
        'phone',
        'id_number',
        'address',
        'complaint_type',
        'complaint_type_other',
        'incident_date',
        'incident_location',
        'officer_name',
        'officer_badge',
        'description',
        'attachments',
        'confiscated_items',
        'confiscated_value',
        'confiscation_reason',
        'confiscation_location',
        'priority',
        'status',
        'assigned_to',
        'assigned_department',
        'assigned_at',
        'investigation_notes',
        'resolution',
        'resolved_at',
        'resolved_by',
        'last_viewed_by_complainant',
        'view_count',
        'is_anonymous',
        'evidence_description'
    ];

    protected $casts = [
        'attachments' => 'array',
        'incident_date' => 'date',
        'assigned_at' => 'datetime',
        'resolved_at' => 'datetime',
        'last_viewed_by_complainant' => 'datetime',
        'is_anonymous' => 'boolean',
        'confiscated_value' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($complaint) {
            // Generate unique ticket number: CMP-20240306-123456
            $complaint->ticket_number = 'CMP-' . date('Ymd') . '-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
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

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function updates()
    {
        return $this->morphMany(CaseUpdate::class, 'caseable');
    }

    /*
    public function communications()
    {
        return $this->morphMany(CaseCommunication::class, 'caseable');
    }
    */

    public function assignments()
    {
        return $this->morphMany(CaseAssignment::class, 'caseable');
    }

    public function escalations()
    {
        return $this->morphMany(Escalation::class, 'caseable');
    }

    // Status History
    public function statusHistory()
    {
        return $this->morphMany(CaseUpdate::class, 'caseable')->where('update_type', 'status_change');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['under_review', 'assigned', 'investigating']);
    }

    public function scopeResolved($query)
    {
        return $query->whereIn('status', ['resolved', 'closed']);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    public function scopeByTicket($query, $ticketNumber)
    {
        return $query->where('ticket_number', $ticketNumber);
    }

    // Accessors
    public function getStatusNameAttribute()
    {
        $statuses = [
            'pending' => 'በመጠባበቅ ላይ',
            'under_review' => 'በግምገማ ላይ',
            'assigned' => 'ተመድቧል',
            'investigating' => 'ምርመራ በሂደት ላይ',
            'resolved' => 'ተፈትቷል',
            'closed' => 'ተዘግቷል',
            'reopened' => 'እንደገና ተከፍቷል'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getPriorityNameAttribute()
    {
        $priorities = [
            'low' => 'ዝቅተኛ',
            'medium' => 'መካከለኛ',
            'high' => 'ከፍተኛ',
            'critical' => 'በጣም አስቸኳይ'
        ];

        return $priorities[$this->priority] ?? $this->priority;
    }

    public function getComplaintTypeNameAttribute()
    {
        $types = [
            'illegal_trade' => 'ህገ-ወጥ ንግድ',
            'corruption' => 'ሙስና',
            'misconduct' => 'የፖሊስ/የሰራተኛ ጥፋት',
            'property_dispute' => 'የንብረት ክርክር',
            'harassment' => 'ትንኮሳ/ማስፈራራት',
            'fraud' => 'ማጭበርበር',
            'environmental' => 'የአካባቢ ጥሰት',
            'other' => 'ሌላ'
        ];

        return $types[$this->complaint_type] ?? $this->complaint_type;
    }
}

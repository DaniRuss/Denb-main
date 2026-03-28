<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConfiscatedAsset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'volunteer_tip_id', 'item_description', 'estimated_value', 'seizure_location',
        'seized_by', 'seizure_date', 'handover_status', 'notes'
    ];

    protected $casts = [
        'seizure_date' => 'date',
        'estimated_value' => 'decimal:2'
    ];

    public function volunteerTip()
    {
        return $this->belongsTo(VolunteerTip::class);
    }

    public function seizedBy()
    {
        return $this->belongsTo(User::class, 'seized_by');
    }
}

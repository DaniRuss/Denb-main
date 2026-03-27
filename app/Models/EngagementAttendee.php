<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EngagementAttendee extends Model
{
    protected $fillable = ['engagement_id', 'name_am', 'gender', 'age'];

    public function engagement()
    {
        return $this->belongsTo(AwarenessEngagement::class);
    }
}

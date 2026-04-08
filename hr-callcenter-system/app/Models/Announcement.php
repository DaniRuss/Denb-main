<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $guarded = [];

    protected $casts = [
        'publish_date' => 'datetime',
        'is_urgent' => 'boolean',
        'is_active' => 'boolean',
    ];
}

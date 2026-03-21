<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EscalationLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'level',
        'response_time_hours',
        'resolution_time_hours',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'json',
    ];
}

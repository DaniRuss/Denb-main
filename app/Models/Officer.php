<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Officer extends Model
{
    use HasFactory;

    protected $fillable = [
        'badge_number',
        'user_id',
        'department_id',
        'rank',
        'rank_am',
        'specialization',
        'phone',
        'status',
        'date_joined',
        'notes',
    ];

    protected $casts = [
        'date_joined' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function assignments()
    {
        return $this->hasMany(CaseAssignment::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->user?->name ?? 'Unknown Officer';
    }
}

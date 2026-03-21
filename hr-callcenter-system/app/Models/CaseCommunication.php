<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Added this line

class CaseCommunication extends Model
{
    use HasFactory; // Added this line

    protected $fillable = [
        'caseable_type',
        'caseable_id',
        'user_id',
        'message',
        'direction',
        'channel',
        'contact_email',
        'contact_phone',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'json',
    ];

    public function caseable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

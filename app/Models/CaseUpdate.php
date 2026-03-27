<?php
// app/Models/CaseUpdate.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'caseable_id',
        'caseable_type',
        'user_id',
        'update_type',
        'message',
        'attachments',
        'is_public',
        'notify_complainant',
        'notified_at'
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_public' => 'boolean',
        'notify_complainant' => 'boolean',
        'notified_at' => 'datetime',
    ];

    public function caseable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeStatusChanges($query)
    {
        return $query->where('update_type', 'status_change');
    }
}

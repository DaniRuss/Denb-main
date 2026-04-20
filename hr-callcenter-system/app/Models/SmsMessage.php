<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SmsMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'to',
        'raw_phone',
        'body',
        'template_key',
        'driver',
        'notifiable_type',
        'notifiable_id',
        'violator_id',
        'status',
        'provider_message_id',
        'error',
        'sent_at',
        'delivered_at',
        'meta',
    ];

    protected $casts = [
        'meta'         => 'array',
        'sent_at'      => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function violator(): BelongsTo
    {
        return $this->belongsTo(Violator::class);
    }

    public function markSent(?string $providerMessageId = null): void
    {
        $this->update([
            'status'              => 'sent',
            'provider_message_id' => $providerMessageId,
            'sent_at'             => now(),
        ]);
    }

    public function markDelivered(): void
    {
        $this->update([
            'status'       => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    public function markFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error'  => $error,
        ]);
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}

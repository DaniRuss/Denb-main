<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            static::recordActivity('created', $model, null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $changed = $model->getChanges();
            unset($changed['updated_at']);

            if (empty($changed)) {
                return;
            }

            $old = collect($changed)->mapWithKeys(
                fn ($v, $k) => [$k => $model->getOriginal($k)]
            )->all();

            static::recordActivity('updated', $model, $old, $changed);
        });

        static::deleted(function ($model) {
            static::recordActivity('deleted', $model, $model->getAttributes(), null);
        });
    }

    protected static function recordActivity(string $action, $model, ?array $old, ?array $new): void
    {
        try {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'model_type' => $model->getMorphClass(),
                'model_id' => $model->getKey(),
                'old_values' => $old,
                'new_values' => $new,
                'ip_address' => request()?->ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // Silently fail — audit logging should never break the main operation
        }
    }
}

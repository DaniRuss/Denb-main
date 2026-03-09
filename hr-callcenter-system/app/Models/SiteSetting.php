<?php
// app/Models/SiteSetting.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'sort_order', 'is_translatable'];

    protected $casts = [
        'is_translatable' => 'boolean',
    ];

    /**
     * Get a setting by key.
     * Use cache for performance.
     */
    public static function get($key, $default = null)
    {
        $settings = Cache::remember('site_settings', 3600, function () {
            return self::pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }

    /**
     * Set a setting by key.
     * Clears cache on update.
     */
    public static function set($key, $value, $type = 'text', $group = 'general')
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'group' => $group]
        );

        Cache::forget('site_settings');
        Cache::forget('all_site_settings');

        return $setting;
    }

    /**
     * Get settings by group.
     */
    public static function getGroup($group)
    {
        return Cache::remember("site_settings_group_{$group}", 3600, function () use ($group) {
            return self::where('group', $group)->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Boot the model to handle cache clearing.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('site_settings');
            Cache::forget('all_site_settings');
            Cache::forget('site_settings_group_general');
            Cache::forget('site_settings_group_contact');
            Cache::forget('site_settings_group_appearance');
            Cache::forget('site_settings_group_hero');
            Cache::forget('site_settings_group_seo');
            Cache::forget('site_settings_group_features');
        });

        static::deleted(function () {
            Cache::forget('site_settings');
            Cache::forget('all_site_settings');
        });
    }
}

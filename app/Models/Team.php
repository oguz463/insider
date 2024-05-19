<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'strength'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }

    /**
     * Get cached teams.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getCachedTeams()
    {
        return Cache::rememberForever('teams', function () {
            return static::all();
        });
    }

    /**
     * Clear the teams cache.
     *
     * @return void
     */
    public static function clearCache()
    {
        Cache::forget('teams');
    }
}

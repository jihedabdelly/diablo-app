<?php

namespace App;

use App\Rankings\Services\Hero\HeroService;
use App\Traits\Queueable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property bool queued
 */
class Hero extends Model
{
    use Queueable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'profile_id',
        'battlenet_hero_id',
        'battle_tag',
        'class', 
        'gender',
        'season',
        'type',
        'paragon_level',
        'kills',
        'clan_tag',
        'clan_name',
        'region',
        'name',
        'hardcore'
    ];

    /**
     * Dates
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'queued_at'
    ];

    /**
     * Casts
     *
     * @var array
     */
    protected $casts = [
        'hardcore' => 'boolean',
        'dead' => 'boolean',
        'season' => 'boolean',
        'queued' => 'boolean'
    ];

    /**
     * @var array
     */
    protected $appends = [
        'queueable',
        'available_in'
    ];

    /**
     * Access Hero API
     *
     * @return Heroes
     */
    public function api()
    {
        return new HeroService($this);
    }

    /**
     * A Hero belongs to a Profile
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * A Hero has many Leaderboards
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function leaderboards()
    {
        return $this->hasMany(Leaderboard::class);
    }

    /**
     * A Profile has many Rift Rankings
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function seasonRankings()
    {
        return $this->belongsToMany(Leaderboard::class)
            ->where('season', '=', true)
            ->where('period', '=', env('CURRENT_SEASON'))
            ->where('players', '!=', 0)
            ->orderBy('players', 'asc')
            ->orderBy('rift_level', 'desc')
            ->orderBy('rift_timestamp', 'desc');
    }

    /**
     * A Hero belongs to many Items
     *
     * @return $this
     */
    public function items()
    {
        return $this->belongsToMany(Item::class)
            ->orderByRaw("field(slot, 'shoulder', 'head', 'neck', 'hands', 'chest', 'bracers', 'finger', 'legs', 'left-hand', 'waist', 'right-hand', 'feet')")
            ->withPivot('tool_tip_params');
    }

    /**
     * A Hero has one stats
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function stats()
    {
        return $this->hasOne(HeroStat::class);
    }

    /**
     * A Hero belongs to many powers
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function powers()
    {
        return $this->belongsToMany(Item::class, 'hero_legendary_power');
    }

    /**
     * A Hero belongs to many skills
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function skills()
    {
        return $this->belongsToMany(Skill::class)
            ->leftJoin('runes as runes', function($join) {
                $join->on('hero_skill.rune_id', '=', 'runes.id');
            })->select('skills.*', 'runes.name as rune', 'runes.type as rune_type');
    }
}

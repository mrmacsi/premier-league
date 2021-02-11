<?php

namespace App\Models;

use App\Events\MatchSaved;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Match extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $dispatchesEvents = [
        'saved' => MatchSaved::class,
    ];

    /**
     * @return HasOne
     */
    public function homeTeam()
    {
        return $this->hasOne(Team::class,'id','home_team_id');
    }

    /**
     * @return HasOne
     */
    public function competitorTeam()
    {
        return $this->hasOne(Team::class,'id','competitor_team_id');
    }

}

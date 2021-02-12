<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class League extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'points',
        'played',
        'win',
        'draw',
        'goal_difference',
        'lose'
    ];

    /**
     * @return HasOne
     */
    public function team()
    {
        return $this->hasOne(Team::class,'id','team_id');
    }
}

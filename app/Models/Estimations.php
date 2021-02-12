<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Estimations extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'team_id',
        'week',
        'change_to_win'
    ];

    /**
     * @return HasOne
     */
    public function team()
    {
        return $this->hasOne(Team::class,'id','team_id');
    }
}

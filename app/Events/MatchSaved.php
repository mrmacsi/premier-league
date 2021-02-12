<?php

namespace App\Events;

use App\Models\League;
use App\Models\Match;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MatchSaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     * @param  Match  $match
     */
    public function __construct(Match $match)
    {
        $homeScore = $match->home_team_score;
        $competitorScore = $match->competitor_team_score;
        if ($homeScore !== null && $competitorScore !== null) {
            $competitorWin = 0;
            $homeWin = 0;
            $competitorDraw = 0;
            $homeDraw = 0;
            $competitorLose = 0;
            $homeLose = 0;
            $homePoint = 0;
            $competitorPoint = 0;
            if ($homeScore > $competitorScore) {
                $homePoint = 3;
                $homeWin++;
                $competitorLose++;
            } elseif ($homeScore < $competitorScore) {
                $competitorPoint = 3;
                $competitorWin++;
                $homeLose++;
            } elseif ($homeScore == $competitorScore) {
                $homePoint = 1;
                $competitorPoint = 1;
                $competitorDraw++;
                $homeDraw++;
            }
            $competitorWeekChart = League::updateOrCreate(['team_id' => $match->competitor_team_id]);
            $competitorWeekChart->points += $competitorPoint;
            $competitorWeekChart->played = $match->week;
            $competitorWeekChart->win += $competitorWin;
            $competitorWeekChart->draw += $competitorDraw;
            $competitorWeekChart->lose += $competitorLose;
            $competitorWeekChart->goal_difference += $competitorScore - $homeScore;
            $competitorWeekChart->save();
            $homeWeekChart = League::updateOrCreate(['team_id' => $match->home_team_id]);
            $homeWeekChart->points += $homePoint;
            $homeWeekChart->played = $match->week;
            $homeWeekChart->win += $homeWin;
            $homeWeekChart->draw += $homeDraw;
            $homeWeekChart->lose += $homeLose;
            $homeWeekChart->goal_difference += $homeScore - $competitorScore;
            $homeWeekChart->save();
        }
    }

    /**
     * Get the channels the event should broadcast on.
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}

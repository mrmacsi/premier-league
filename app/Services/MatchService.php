<?php

namespace App\Services;

use App\Models\Estimations;
use App\Models\League;
use App\Models\Match;
use App\Models\Team;
use App\Services\Interfaces\MatchServiceInterface;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MatchService implements MatchServiceInterface
{
    /**
     * @return Collection
     */
    public function getAllTeams(): Collection
    {
        return Team::all();
    }

    /**
     * @return int
     */
    public function getTotalTeamCount(): int
    {
        return Team::all()
            ->count();
    }

    /**
     * @return int
     */
    public function getTotalWeeks(): int
    {
        $total = $this->getTotalTeamCount();
        return ($total * ($total - 1)) / 2;
    }

    /**
     * @return Collection
     */
    public function getWeeklyFixture(): Collection
    {
        $weeklyMatches = collect();
        $teams = $this->getAllTeams();
        $matrix = $teams->crossJoin($teams)
            ->filter(function ($items) {
                $items = collect($items);
                return $items->first()->id != $items->last()->id;
            })
            ->collect();
        foreach ($matrix as $key => $matches) {
            if (isset($matrix[$key])) {
                $weeklyMatches->push([$matches[0], $matches[1]]);
                $restOfTheMatches = $teams->except(collect($matches)
                    ->pluck('id')
                    ->toArray());
                $index = $matrix->search(function ($item) use ($matches) {
                    return $item[0]->id == $matches[0]->id && $item[1]->id == $matches[1]->id;
                });
                $matrix->forget($index);
                $index = $matrix->search(function ($item) use ($restOfTheMatches) {
                    return $item[0]->id == $restOfTheMatches[0]->id && $item[1]->id == $restOfTheMatches[1]->id;
                });
                if ( !$index) {
                    $index = $matrix->search(function ($item) use ($restOfTheMatches) {
                        return $item[0]->id == $restOfTheMatches[1]->id && $item[1]->id == $restOfTheMatches[0]->id;
                    });
                    $weeklyMatches->push([$restOfTheMatches[1], $restOfTheMatches[0]]);
                    $matrix->forget($index);
                } else {
                    $weeklyMatches->push([$restOfTheMatches[0], $restOfTheMatches[1]]);
                    $matrix->forget($index);
                }
            }
        }
        $weeklyMatches = $weeklyMatches->chunk(2);
        return $weeklyMatches;
    }

    /**
     * @return bool
     */
    public function saveAllFixtureMatches(): bool
    {
        try {
            $allWeekFixture = $this->getWeeklyFixture();
            foreach ($allWeekFixture as $key => $weeklyMatches) {
                foreach ($weeklyMatches as $matches) {
                    $match = new Match();
                    $match->home_team_id = collect($matches)->first()->id;
                    $match->competitor_team_id = collect($matches)->last()->id;
                    $match->week = $key + 1;
                    $match->save();
                }
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param  int  $week
     * @return bool
     */
    public function matchTheTeamsByWeek(int $week): bool
    {
        if ( !$week) {
            return false;
        }
        try {
            DB::beginTransaction();
            $matches = Match::where(['week' => $week])
                ->get();
            foreach ($matches as $match) {
                $randHome = (float) rand() / (float) getrandmax();
                $randCompetitor = (float) rand() / (float) getrandmax();
                $competitorStrength = $match->competitorTeam()
                                          ->get()
                                          ->pluck('strength')[0];
                $competitorScore = (int) round($competitorStrength * $randCompetitor);
                $homeStrength = $match->homeTeam()
                                    ->get()
                                    ->pluck('strength')[0];
                $homeScore = (int) round($homeStrength * $randHome);
                $homeScore = $homeScore > 3 ? 3 : $homeScore;
                $competitorScore = $competitorScore > 3 ? 3 : $competitorScore;
                $match->competitor_team_score = $competitorScore;
                $match->home_team_score = $homeScore;
                $match->save();
            }
            $this->generateEstimations($week);
            //all good
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }
        return true;
    }

    public function generateEstimations($week): bool
    {
        //sometimes calculation is more than 100 percent but it doesn't mean I can't fix when I have time :)
        $allLeague = $this->getAllLeague();
        $totalPoints = $allLeague->sum('points');
        $firstOnesPoint = $allLeague->first()->points;
        $secondOnesPoint = $allLeague->get(2)->points;
        $totalWeeks = $this->getTotalWeeks();
        $mostPointToGet = ($totalWeeks-$week)*3;
        foreach ($allLeague as $key => $team) {
            $estimationPercent = round(100 * $team->points / $totalPoints);
            if ($key > 0) {
                $cantWin = $firstOnesPoint > ($team->points + $mostPointToGet);
                $estimationPercent = $cantWin ? 0 : $estimationPercent;
            }else{
                //no one can beat you ;)
                $winAlready = $team->points > ($secondOnesPoint + $mostPointToGet);
                $estimationPercent = $winAlready ? 100 : $estimationPercent;
            }
            Estimations::updateOrCreate([
                'team_id'       => $team->team_id,
                'week'          => $week,
                'chance_to_win' => $estimationPercent
            ]);
        }
        return true;
    }

    public function getAllLeague(): Collection
    {
        return League::orderBy('points', 'desc')
            ->get();
    }

    public function getWeeklyChanceToWin($week): Collection
    {
        return Estimations::where(['week' => $week])
            ->orderBy('chance_to_win', 'desc')
            ->get();
    }

    public function getLeagueTable($week): array
    {
        $estimations = $this->getWeeklyChanceToWin($week);
        $estimationsMapped = $estimations->map(function ($estimation) {
            $team = $estimation->team()
                ->get()
                ->first();
            return [
                'team_name'     => $team->team_name,
                'chance_to_win' => $estimation->chance_to_win
            ];
        });
        $allTeams = $this->getAllTeams()
            ->toArray();
        $totalWeeks = $this->getTotalWeeks();
        $matches = Match::where(['week' => $week])
            ->get();
        $mappedMatches = $matches->map(function ($match) {
            $homeTeam = $match->homeTeam()
                ->get()
                ->first();
            $competitorTeam = $match->competitorTeam()
                ->get()
                ->first();
            return [
                'home_team_name'        => $homeTeam->team_name,
                'home_team_score'       => $match->home_team_score,
                'competitor_team_name'  => $competitorTeam->team_name,
                'competitor_team_score' => $match->competitor_team_score,
            ];
        });
        $league = League::orderBy('points', 'desc')
            ->get();
        $mappedLeague = $league->map(function ($item) {
            $team = $item->team()
                ->get()
                ->first();
            return [
                'team_id'         => $team->id,
                'team_name'       => $team->team_name,
                'points'          => $item->points,
                'played'          => $item->played,
                'win'             => $item->win,
                'draw'            => $item->draw,
                'lose'            => $item->lose,
                'goal_difference' => $item->goal_difference
            ];
        });
        $result['stats'] = $mappedLeague;
        $result['matches'] = $mappedMatches;
        $result['week'] = $week;
        $result['totalWeeks'] = $totalWeeks;
        $result['allTeams'] = $allTeams;
        $result['estimations'] = $estimationsMapped;
        return $result;
    }

    public function clean($week)
    {
        if ($week == 1) {
            League::truncate();
            Match::truncate();
            Team::truncate();
            Estimations::truncate();
            $this->seedDataForTeams();
            $this->saveAllFixtureMatches();
            return true;
        }
        return false;
    }

    public function seedDataForTeams()
    {
        $data = [
            ['team_name' => 'Chelsea', 'strength' => 2.9],
            ['team_name' => 'Liverpool', 'strength' => 1.9],
            ['team_name' => 'Manchester', 'strength' => 1.6],
            ['team_name' => 'Arsenal', 'strength' => 2.1],
        ];
        Team::insert($data);
    }
}

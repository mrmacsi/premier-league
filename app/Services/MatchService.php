<?php

namespace App\Services;

use App\Models\League;
use App\Models\Match;
use App\Models\Team;
use App\Services\Interfaces\MatchServiceInterface;
use Exception;
use Illuminate\Support\Collection;

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
        return true;
    }

    public function getLeagueTable($week): array
    {
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
        return $result;
    }

    public function clean($week)
    {
        if ($week == 1) {
            League::truncate();
            Match::truncate();
            Team::truncate();
            $this->seedDataForTeams();
            $this->saveAllFixtureMatches();
            return true;
        }
        return false;
    }

    public function seedDataForTeams()
    {
        $data = [
            ['team_name' => 'Chelsea', 'strength' => 3],
            ['team_name' => 'Liverpool', 'strength' => 1],
            ['team_name' => 'Manchester', 'strength' => 1],
            ['team_name' => 'Arsenal', 'strength' => 2],
        ];
        Team::insert($data);
    }
}

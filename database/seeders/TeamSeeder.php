<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Services\Interfaces\MatchServiceInterface;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @param  MatchServiceInterface  $matchService
     * @return void
     */
    public function run(MatchServiceInterface $matchService)
    {
        $data = [
            ['team_name'=>'Chelsea','strength' => 3],
            ['team_name'=>'Liverpool','strength' => 1],
            ['team_name'=>'Manchester','strength' => 1],
            ['team_name'=>'Arsenal','strength' => 2],
        ];

        Team::insert($data);
        $matchService->saveAllFixtureMatches();
    }
}

<?php

namespace Database\Seeders;

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
        $matchService->seedDataForTeams();
        $matchService->saveAllFixtureMatches();
    }
}

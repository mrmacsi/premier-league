<?php

namespace App\Services\Interfaces;

use Illuminate\Support\Collection;

interface MatchServiceInterface
{
    public function seedDataForTeams();

    public function clean($week);

    public function getLeagueTable($week): array;

    public function matchTheTeamsByWeek(int $week): bool;

    public function saveAllFixtureMatches(): bool;

    public function getWeeklyFixture(): Collection;

    public function getTotalWeeks(): int;

    public function getTotalTeamCount(): int;

    public function getAllTeams(): Collection;
}

<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\MatchServiceInterface;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function index(Request $request, $week, MatchServiceInterface $matchService)
    {
        $matchService->clean($week);
        $matchService->matchTheTeamsByWeek($week);
        return $matchService->getLeagueTable($week);
    }
}

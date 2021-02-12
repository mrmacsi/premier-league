<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\MatchServiceInterface;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function index(Request $request,MatchServiceInterface $matchService)
    {
        $week = 5;
        $matchService->matchTheTeamsByWeek($week);
        return $matchService->getLeagueTable($week);
    }
}

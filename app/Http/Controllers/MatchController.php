<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\MatchServiceInterface;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function index(Request $request,MatchServiceInterface $matchService)
    {
        $result = $matchService->matchTheTeamsByWeek(2);
    }
}

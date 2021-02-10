<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['team_name'=>'Chelsea'],
            ['team_name'=>'Liverpool'],
            ['team_name'=>'Manchester'],
            ['team_name'=>'Arsenal'],
        ];

        Team::insert($data); // Eloquent approach
    }
}

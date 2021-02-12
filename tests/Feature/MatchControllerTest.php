<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchControllerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get(route('match.index',['week'=>1]));
        $response->assertJsonStructure([
            'matches',
            'stats',
            'week'
        ]);
        $response->assertJson([
            'week'=>1
        ]);

        $response->assertStatus(200);
    }
}

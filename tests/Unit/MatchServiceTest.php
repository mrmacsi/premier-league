<?php

namespace Tests\Unit;

use App\Services\Interfaces\MatchServiceInterface;
use PHPUnit\Framework\TestCase;

class MatchServiceTest extends TestCase
{
    public $matchService;

    public function __construct()
    {
        $this->matchService = app(MatchServiceInterface::class);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testClean()
    {
        $this->matchService->clean();
        $this->assertTrue(true);
    }
}

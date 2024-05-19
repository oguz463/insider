<?php

namespace Tests\Unit;

use App\Models\Team;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a team without triggering events.
     *
     * @return \App\Models\Team
     */
    private function teamWithoutEvents()
    {
        return Team::withoutEvents(function () {
            return Team::factory()->create();
        });
    }

    public function testClearsCacheOnCreate()
    {
        Cache::shouldReceive('forget')
            ->once()
            ->with('teams');

        Team::factory()->create();
    }

    public function testClearsCacheOnUpdate()
    {
        Cache::shouldReceive('forget')
            ->once()
            ->with('teams');

        $team = $this->teamWithoutEvents();
        $team->update(['name' => 'Updated Name']);
    }

    public function testClearsCacheOnDelete()
    {
        Cache::shouldReceive('forget')
            ->once()
            ->with('teams');

        $team = $this->teamWithoutEvents();
        $team->delete();
    }

    public function testClearsCacheOnSave()
    {
        Cache::shouldReceive('forget')
            ->once()
            ->with('teams');

        $team = $this->teamWithoutEvents();
        $team->save();
    }
}

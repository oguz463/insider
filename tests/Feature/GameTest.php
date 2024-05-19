<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedTeams();
        $this->seedFixtures();
    }

    /**
     * Seed the teams.
     *
     * @return void
     */
    private function seedTeams()
    {
        Team::factory()->count(4)->create();
    }

    /**
     * Seed the fixtures.
     *
     * @return void
     */
    private function seedFixtures()
    {
        $teams = Team::all();
        $fixtures = [];

        foreach ($teams as $homeTeam) {
            foreach ($teams as $awayTeam) {
                if ($homeTeam->id !== $awayTeam->id) {
                    $fixtures[] = [
                        'home_team_id' => $homeTeam->id,
                        'away_team_id' => $awayTeam->id
                    ];
                }
            }
        }

        shuffle($fixtures);

        foreach (array_chunk($fixtures, 2) as $week) {
            foreach ($week as $fixture) {
                Game::create($fixture);
            }
        }
    }

    public function testCanListAllGames()
    {
        $response = $this->getJson('/api/games');
        $response->assertStatus(200);
        $this->assertCount(12, $response->json());
    }

    public function testCanCreateFixtures()
    {
        $response = $this->postJson('/api/generate-fixtures');
        $response->assertStatus(200);
        $this->assertCount(12, $response->json());
    }

    public function testCanPlayNextWeek()
    {
        $response = $this->postJson('/api/play-next-week');
        $response->assertStatus(200);

        $data = collect($response->json()['leagueTable']);
        $teamPlayedMatchCount = $data->pluck('p')->max();

        $this->assertEquals(1, $teamPlayedMatchCount);
    }

    public function testCanPlayAllWeeks()
    {
        $response = $this->postJson('/api/play-all-weeks');
        $response->assertStatus(200);

        $data = collect($response->json()['leagueTable']);
        $teamPlayedMatchCount = $data->pluck('p')->max();

        $this->assertEquals(6, $teamPlayedMatchCount);
    }

    public function testCanResetData()
    {
        $this->postJson('/api/play-next-week'); // Play one week to have some scores
        $response = $this->postJson('/api/reset-data');
        $response->assertStatus(200);

        $data = collect($response->json()['leagueTable']);
        $teamPlayedMatchCount = $data->pluck('p')->max();
        $teamPointCount = $data->pluck('points')->max();

        $this->assertEquals(0, $teamPlayedMatchCount);
        $this->assertEquals(0, $teamPointCount);
    }

    public function testCanGetSimulationData()
    {
        $response = $this->getJson('/api/simulation-data');
        $response->assertStatus(200);
        $data = $response->json();

        $this->assertArrayHasKey('leagueTable', $data);
        $this->assertArrayHasKey('fixtures', $data);
        $this->assertArrayHasKey('predictions', $data);
        $this->assertCount(12, $data['fixtures']);
    }

    public function testCorrectlyCalculatesLeagueTable()
    {
        $this->postJson('/api/play-all-weeks');

        $response = $this->getJson('/api/simulation-data');
        $response->assertStatus(200);

        $data = $response->json();
        $leagueTable = collect($data['leagueTable']);

        $this->assertEquals(4, $leagueTable->count());

        $leagueTable->each(function ($team) {
            $this->assertGreaterThanOrEqual(0, $team['p']);
            $this->assertGreaterThanOrEqual(0, $team['w']);
            $this->assertGreaterThanOrEqual(0, $team['d']);
            $this->assertGreaterThanOrEqual(0, $team['l']);
            $this->assertGreaterThanOrEqual(-24, $team['gd']); // Consider the possible negative value of GD
            $this->assertGreaterThanOrEqual(0, $team['points']);
        });
    }

    public function testCorrectlyCalculatesPredictions()
    {
        $this->postJson('/api/play-all-weeks');

        $response = $this->getJson('/api/simulation-data');
        $response->assertStatus(200);

        $data = $response->json();
        $predictions = collect($data['predictions']);

        $this->assertEquals(4, $predictions->count());

        $champion = $predictions->first();
        $this->assertEquals(100, $champion['percentage']);

        $predictions->skip(1)->each(function ($prediction) {
            $this->assertEquals(0, $prediction['percentage']);
        });
    }
}

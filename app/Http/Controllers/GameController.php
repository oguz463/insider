<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Team;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    /**
     * Display a listing of the games.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(Game::with(['homeTeam', 'awayTeam'])->get());
    }

    /**
     * Create fixtures for the teams.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createFixtures(): JsonResponse
    {
        $teams = Team::getCachedTeams();
        $fixtures = $this->generateRandomFixtures($teams);

        Game::truncate();

        foreach ($fixtures as $week) {
            foreach ($week as $fixture) {
                Game::create([
                    'home_team_id' => $fixture['home_team_id'],
                    'away_team_id' => $fixture['away_team_id']
                ]);
            }
        }

        return response()->json(Game::with(['homeTeam', 'awayTeam'])->get());
    }

    /**
     * Generate random fixtures for the given teams.
     *
     * @param \Illuminate\Database\Eloquent\Collection $teams
     * @return array
     */
    private function generateRandomFixtures($teams): array
    {
        $fixtures = [];
        $teamCount = count($teams);
        $teamIds = $teams->pluck('id')->toArray();

        // Generate all possible matches
        $allFixtures = [];
        for ($i = 0; $i < $teamCount; $i++) {
            for ($j = $i + 1; $j < $teamCount; $j++) {
                $allFixtures[] = [
                    'home_team_id' => $teamIds[$i],
                    'away_team_id' => $teamIds[$j]
                ];
                $allFixtures[] = [
                    'home_team_id' => $teamIds[$j],
                    'away_team_id' => $teamIds[$i]
                ];
            }
        }

        // Shuffle fixtures to randomize the order
        shuffle($allFixtures);

        // Group fixtures into weeks ensuring each team plays only once per week
        while (!empty($allFixtures)) {
            $week = [];
            $teamsInWeek = [];
            foreach ($allFixtures as $key => $fixture) {
                if (!in_array($fixture['home_team_id'], $teamsInWeek) && !in_array($fixture['away_team_id'], $teamsInWeek)) {
                    $week[] = $fixture;
                    $teamsInWeek[] = $fixture['home_team_id'];
                    $teamsInWeek[] = $fixture['away_team_id'];
                    unset($allFixtures[$key]);
                }
            }
            $fixtures[] = $week;
        }

        return $fixtures;
    }

    /**
     * Play the next week of games.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function playNextWeek(): JsonResponse
    {
        $willTake = Team::getCachedTeams()->count() / 2;
        $nextWeekGames = Game::whereNull('home_team_score')
            ->whereNull('away_team_score')
            ->take($willTake)
            ->with(['homeTeam', 'awayTeam'])
            ->get();

        if ($nextWeekGames->isEmpty()) {
            return response()->json(['message' => 'All games have been played'], 200);
        }

        $teamsPlayed = [];
        foreach ($nextWeekGames as $game) {
            if (!in_array($game->home_team_id, $teamsPlayed) && !in_array($game->away_team_id, $teamsPlayed)) {
                $homeTeamStrength = $game->homeTeam->strength;
                $awayTeamStrength = $game->awayTeam->strength;

                $homeTeamScore = $this->generateScore($homeTeamStrength);
                $awayTeamScore = $this->generateScore($awayTeamStrength);

                $game->update([
                    'home_team_score' => $homeTeamScore,
                    'away_team_score' => $awayTeamScore,
                ]);

                $teamsPlayed[] = $game->home_team_id;
                $teamsPlayed[] = $game->away_team_id;
            }
        }

        return response()->json($this->getSimulationData());
    }

    /**
     * Play all weeks of games.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function playAllWeeks(): JsonResponse
    {
        while (Game::whereNull('home_team_score')->whereNull('away_team_score')->exists()) {
            $games = Game::whereNull('home_team_score')
                ->whereNull('away_team_score')
                ->with(['homeTeam', 'awayTeam'])
                ->get();

            $teamsPlayed = [];
            foreach ($games as $game) {
                if (!in_array($game->home_team_id, $teamsPlayed) && !in_array($game->away_team_id, $teamsPlayed)) {
                    $homeTeamStrength = $game->homeTeam->strength;
                    $awayTeamStrength = $game->awayTeam->strength;

                    $homeTeamScore = $this->generateScore($homeTeamStrength);
                    $awayTeamScore = $this->generateScore($awayTeamStrength);

                    $game->update([
                        'home_team_score' => $homeTeamScore,
                        'away_team_score' => $awayTeamScore,
                    ]);

                    $teamsPlayed[] = $game->home_team_id;
                    $teamsPlayed[] = $game->away_team_id;
                }
            }
        }

        return response()->json($this->getSimulationData());
    }

    /**
     * Generate a random score based on team strength.
     *
     * @param int $strength
     * @return int
     */
    private function generateScore(int $strength): int
    {
        $prob = rand(0, 100);
        $score = 0;

        if ($prob < 60) {
            $score = 0; // 60% chance of scoring 0 goals
        } elseif ($prob < 85) {
            $score = 1; // 25% chance of scoring 1 goal
        } elseif ($prob < 95) {
            $score = 2; // 10% chance of scoring 2 goals
        } elseif ($prob < 99) {
            $score = 3; // 4% chance of scoring 3 goals
        } else {
            $score = 4; // 1% chance of scoring 4 goals
        }

        // Adjust score slightly based on team strength
        if ($strength > 50 && rand(0, 100) < ($strength - 50)) {
            $score++;
        }

        return $score;
    }

    /**
     * Reset the game data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetData(): JsonResponse
    {
        Game::query()->update(['home_team_score' => null, 'away_team_score' => null]);

        return response()->json($this->getSimulationData());
    }

    /**
     * Get the simulation data including league table, fixtures, and predictions.
     *
     * @return array
     */
    public function getSimulationData()
    {
        $teams = Team::getCachedTeams();
        $leagueTable = $teams->mapWithKeys(function ($team) {
            return [
                $team->id => [
                    'id' => $team->id,
                    'name' => $team->name,
                    'p' => 0,
                    'w' => 0,
                    'd' => 0,
                    'l' => 0,
                    'gd' => 0,
                    'points' => 0,
                    'max_points' => 0,
                    'strength' => $team->strength
                ]
            ];
        });

        $games = Game::with(['homeTeam', 'awayTeam'])->get();

        foreach ($games as $game) {
            if ($game->home_team_score !== null && $game->away_team_score !== null) {
                $homeTeam = $leagueTable->get($game->home_team_id);
                $awayTeam = $leagueTable->get($game->away_team_id);

                if ($homeTeam && $awayTeam) {
                    $homeTeam['p'] += 1;
                    $awayTeam['p'] += 1;

                    if ($game->home_team_score > $game->away_team_score) {
                        $homeTeam['w'] += 1;
                        $homeTeam['points'] += 3;
                        $awayTeam['l'] += 1;
                    } elseif ($game->home_team_score < $game->away_team_score) {
                        $awayTeam['w'] += 1;
                        $awayTeam['points'] += 3;
                        $homeTeam['l'] += 1;
                    } else {
                        $homeTeam['d'] += 1;
                        $homeTeam['points'] += 1;
                        $awayTeam['d'] += 1;
                        $awayTeam['points'] += 1;
                    }

                    $homeTeam['gd'] += $game->home_team_score - $game->away_team_score;
                    $awayTeam['gd'] += $game->away_team_score - $game->home_team_score;

                    $leagueTable[$game->home_team_id] = $homeTeam;
                    $leagueTable[$game->away_team_id] = $awayTeam;
                }
            }
        }

        $sortedLeagueTable = $leagueTable->values()->sort(function ($a, $b) {
            if ($a['points'] === $b['points']) {
                return $b['gd'] <=> $a['gd'];
            }
            return $b['points'] <=> $a['points'];
        });

        $totalGames = $games->count();
        $gamesPlayed = $games->whereNotNull('home_team_score')->whereNotNull('away_team_score')->count();
        $gamesRemaining = $totalGames - $gamesPlayed;

        $predictions = collect([]);
        if ($totalGames > 0) {
            $teams = $teams->keyBy('id');
            $leagueTable->transform(function ($team) use ($gamesRemaining, $teams) {
                $remainingGames = $gamesRemaining / 2; // Each team will have half of the remaining games

                // Calculate maximum possible points
                $team['max_points'] = $team['points'] + ($remainingGames * 3);

                // Fetch team strength from the pre-loaded team data
                $team['strength'] = $teams->get($team['id'])->strength;

                return $team;
            });

            // Calculate the max points of the current leader
            $maxLeaderPoints = $leagueTable->max('points');
            $maxLeaderGD = $leagueTable->where('points', $maxLeaderPoints)->max('gd');

            // Calculate predictions
            $predictions = $leagueTable->map(function ($team) use ($leagueTable, $maxLeaderPoints, $maxLeaderGD, $totalGames, $gamesRemaining) {
                $otherTeamsMaxPoints = $leagueTable->where('id', '!=', $team['id'])->max('max_points');

                // Determine if a team can still win
                if ($team['max_points'] < $maxLeaderPoints) {
                    $percentage = 0;
                } else {
                    // Simple model: prediction based on current points, max points, and team strength
                    $strengthFactor = $team['strength'] / 100;
                    $currentPointsFactor = $totalGames > 0 ? $team['points'] / $totalGames : 0;
                    $maxPointsFactor = $totalGames > 0 ? $team['max_points'] / ($totalGames * 3) : 0;

                    // Calculate the percentage
                    $percentage = round((($currentPointsFactor + $maxPointsFactor + $strengthFactor) / 3) * 100, 2);

                    // Adjust for champion scenarios
                    if ($gamesRemaining === 0) {
                        if ($team['points'] === $maxLeaderPoints && $team['gd'] === $maxLeaderGD) {
                            $percentage = 100;
                        } else {
                            $percentage = 0;
                        }
                    }
                }

                return [
                    'team' => $team['name'],
                    'percentage' => $percentage
                ];
            });

            $predictions = $predictions->sortByDesc('percentage')->values();
        }

        return [
            'leagueTable' => $sortedLeagueTable->values(),
            'fixtures' => $games,
            'predictions' => $predictions
        ];
    }
}

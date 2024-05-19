<?php

namespace Database\Seeders;

use App\Models\Team;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {       
        Team::create(['name' => 'Liverpool', 'strength' => 90]);
        Team::create(['name' => 'Manchester City', 'strength' => 92]);
        Team::create(['name' => 'Chelsea', 'strength' => 88]);
        Team::create(['name' => 'Arsenal', 'strength' => 85]);
    }
}

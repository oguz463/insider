<template>
  <div class="p-4">
    <h1 class="text-center text-3xl font-thin mb-4">Simulation</h1>
    <div class="flex flex-wrap space-x-6 space-y-6 items-baseline justify-center">
      <!-- League Table -->
      <table class="bg-white text-left">
        <thead>
          <tr class="bg-gray-800 text-white">
            <th class="p-4 border-t">Team Name</th>
            <th class="p-4 border-t">P</th>
            <th class="p-4 border-t">W</th>
            <th class="p-4 border-t">D</th>
            <th class="p-4 border-t">L</th>
            <th class="p-4 border-t">GD</th>
            <th class="p-4 border-t">Points</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="team in leagueTable" :key="team.name">
            <td class="p-4 border-t">{{ team.name }}</td>
            <td class="p-4 border-t">{{ team.p }}</td>
            <td class="p-4 border-t">{{ team.w }}</td>
            <td class="p-4 border-t">{{ team.d }}</td>
            <td class="p-4 border-t">{{ team.l }}</td>
            <td class="p-4 border-t">{{ team.gd }}</td>
            <td class="p-4 border-t">{{ team.points }}</td>
          </tr>
        </tbody>
      </table>
      <!-- Current Week Fixtures -->
      <table class="w-max bg-white border border-gray-200 mb-4 text-left">
        <thead>
          <tr class="bg-gray-800 text-white">
            <th class="p-4 border-t text-left" colspan="2">Week {{ currentWeek }}</th>
          </tr>
        </thead>
        <tbody>
          <tr class="relative" v-for="game in fixtures[currentWeek - 1]" :key="game.home_team_id + '-' + game.away_team_id">
            <td class="p-4 border-t text-left">{{ game.home_team.name }}</td>
            <td class="py-4 pl-32 pr-4 border-t text-left">{{ game.away_team.name }}</td>
            <span class="absolute inset-0 flex items-center justify-center">-</span>
          </tr>
        </tbody>
      </table>
      <!-- Predictions -->
      <table class="w-max bg-white text-left">
        <thead>
          <tr class="w-full bg-gray-800 text-white">
            <th class="p-4 border-t">Team Name</th>
            <th class="p-4 border-t">Prediction (%)</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="prediction in predictions" :key="prediction.team">
            <td class="p-4 border-t">{{ prediction.team }}</td>
            <td class="p-4 border-t">{{ prediction.percentage }}%</td>
          </tr>
        </tbody>
      </table>
    </div>
    <!-- Control Buttons -->
    <div class="mt-4 flex justify-evenly">
      <button @click="playNextWeek" class="mt-4 bg-secondary text-white py-2 px-4 rounded" :disabled="disableButton">
        Play Next Week
      </button>
      <button @click="playAllWeeks" class="mt-4 bg-secondary text-white py-2 px-4 rounded" :disabled="disableButton">
        Play All Weeks
      </button>
      <button @click="resetData" class="mt-4 bg-red-500 text-white py-2 px-4 rounded">
        Reset Data
      </button>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      fixtures: [],
      leagueTable: [],
      currentWeek: 1,
      predictions: [],
      disableButton: false
    }
  },
  created() {
    this.fetchSimulationData();
  },
  methods: {
    fetchSimulationData() {
      axios.get('/api/simulation-data')
        .then(response => {
          this.fixtures = this.groupByWeek(response.data.fixtures);
          this.leagueTable = response.data.leagueTable;
          this.predictions = response.data.predictions;
        })
        .catch(error => {
          console.error(error);
        });
    },
    groupByWeek(games) {
      const numTeams = new Set(games.flatMap(game => [game.home_team_id, game.away_team_id])).size;
      const gamesPerWeek = Math.floor(numTeams / 2);

      let weeks = [];
      for (let i = 0; i < games.length; i += gamesPerWeek) {
        weeks.push(games.slice(i, i + gamesPerWeek));
      }
      return weeks;
    },
    playNextWeek() {
      if (this.currentWeek <= this.fixtures.length) {
        axios.post('/api/play-next-week')
          .then(response => {
            this.leagueTable = response.data.leagueTable;
            this.predictions = response.data.predictions;

            if (this.currentWeek === this.fixtures.length) {
              this.disableButton = true;
            }

            if (this.currentWeek < this.fixtures.length) {
              this.currentWeek++;
            }
          })
          .catch(error => {
            console.error(error);
          });
      }
    },
    playAllWeeks() {
      if (this.currentWeek <= this.fixtures.length) {
        axios.post('/api/play-all-weeks')
          .then(response => {
            this.leagueTable = response.data.leagueTable;
            this.predictions = response.data.predictions;
            this.currentWeek = this.fixtures.length;
            this.disableButton = true;
          })
          .catch(error => {
            console.error(error);
          });
      }
    },
    resetData() {
      axios.post('/api/reset-data')
        .then(response => {
          this.currentWeek = 1;
          this.disableButton = false;
          this.fixtures = this.groupByWeek(response.data.fixtures);
          this.leagueTable = response.data.leagueTable;
          this.predictions = response.data.predictions;
        })
        .catch(error => {
          console.error(error);
        });
    }
  }
}
</script>

<template>
  <div>
    <TournamentTeams v-if="step === 1" @generate-fixtures="generateFixtures" />
    <GeneratedFixtures v-if="step === 2" :weeks="fixtures" @start-simulation="startSimulation" />
    <Simulation v-if="step === 3" />
  </div>
</template>

<script>
import TournamentTeams from './components/TournamentTeams.vue';
import GeneratedFixtures from './components/GeneratedFixtures.vue';
import Simulation from './components/Simulation.vue';

export default {
  components: {
    TournamentTeams,
    GeneratedFixtures,
    Simulation,
  },
  data() {
    return {
      step: 1,
      fixtures: [],
    };
  },
  methods: {
    generateFixtures(games) {
      this.fixtures = this.groupByWeek(games);
      this.step = 2;
    },
    startSimulation() {
      this.step = 3;
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
  },
};
</script>

<style>
  .bg-secondary {
    background-color: #19a2ba;
  }
</style>

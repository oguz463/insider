<template>
  <div class="p-4 flex flex-wrap space-x-6 items-center justify-center">
    <div class="w-full flex justify-center">
      <h1 class="text-3xl font-thin mb-4">Generated Fixtures</h1>
    </div>
    <div v-for="(fixtures, week) in weeks" :key="week" class="mb-2">
      <table class="min-w-96 bg-white border border-gray-200 mb-4 text-left">
        <thead>
          <tr class="bg-gray-800 text-white">
            <th class="p-4 border-t" colspan="2">Week {{ week + 1 }}</th>
          </tr>
        </thead>
        <tbody>
          <tr class="relative" v-for="game in fixtures" :key="game.home_team_id + '-' + game.away_team_id">
            <td class="p-4 border-t">{{ game.home_team.name }}</td>
            <td class="py-4 pl-32 pr-4 border-t">{{ game.away_team.name }}</td>
            <span class="absolute inset-0 flex items-center justify-center">-</span>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="w-full flex justify-center">
      <button @click="startSimulation" class="mt-4 bg-secondary text-white py-2 px-4 rounded">Start Simulation</button>
    </div>
  </div>
</template>

<script>
export default {
  props: ['weeks'],
  methods: {
    startSimulation() {
      this.$emit('start-simulation');
    }
  }
}
</script>

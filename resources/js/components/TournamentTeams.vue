<template>
  <div class="p-4 w-screen h-screen flex flex-col items-center justify-center">
    <div>
      <h1 class="text-3xl font-thin mb-4">Tournament Teams</h1>
      <table class="min-w-96 bg-white text-left">
        <thead>
          <tr class="w-full bg-gray-800 text-white">
            <th class="p-4 text-left">Team Name</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="team in teams" :key="team.id">
            <td class="p-4 border-t">{{ team.name }}</td>
          </tr>
        </tbody>
      </table>
      <button @click="generateFixtures" class="bg-secondary text-white py-2 px-4 rounded mt-4">
        Generate Fixtures
      </button>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      teams: []
    }
  },
  created() {
    this.fetchTeams();
  },
  methods: {
    fetchTeams() {
      axios.get('/api/teams')
        .then(response => {
          this.teams = response.data;
        })
        .catch(error => {
          console.error(error);
        });
    },
    generateFixtures() {
      axios.post('/api/generate-fixtures')
        .then(response => {
          this.$emit('generate-fixtures', response.data);
        })
        .catch(error => {
          console.error(error);
        });
    }
  }
}
</script>

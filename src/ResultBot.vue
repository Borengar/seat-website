<template lang="pug">
v-layout(column)
	v-stepper(v-model="step")
		v-stepper-header
			v-stepper-step(:complete="step > 1" step="1") General
			v-divider
			v-stepper-step(:complete="step > 2" step="2") Players
			v-divider
			v-stepper-step(:complete="step > 3" step="3") Bans
			v-divider
			v-stepper-step(:complete="step > 4" step="4") Picks
			v-divider
			v-stepper-step(:complete="step > 5" step="5") Send Result
		v-stepper-items
			v-stepper-content(step="1")
				h1 Select the mappool and match id
				v-select.mr-2.mappool-select(label="Mappool" v-model="selectedMappool"  :items="mappools" item-text="name" item-value="_id")
				v-text-field.mr-2.match-id(label="Match ID" v-model="matchId" @keyup.enter="searchMatch")
				v-btn(@click="searchMatch" color="success"  :disabled="searchDisabled") Continue
			v-stepper-content(step="2")
				h1 Choose 2 players
				v-checkbox(v-for="user in match.users"  :key="user.id"  v-model="user.isPlayer"  :label="user.username")
				v-btn(@click="choosePlayers" color="success"  :disabled="choosePlayersDisabled") Continue
			v-stepper-content(step="3")
				h1 Bans
			v-stepper-content(step="4")
				h1 Picks
			v-stepper-content(step="5")
				h1 Send Result
</template>

<script>
export default {
	name: 'ResultBot',
	data: () => ({
		step: 1,
		selectedMappool: null,
		matchId: null,
		match: {
			events: [],
			users: []
		},
		mappool: {
			_id: null,
			name: null,
			slots: []
		},
		players: [],
		games: []
	}),
	methods: {
		searchMatch() {
			this.axios.get('/api/osumatch/' + this.matchId)
			.then((result) => {
				let match = result.data
				for (let i = match.events.length - 1; i >= 0; i--) {
					if (match.events[i].detail.type != 'other') {
						match.events.splice(i, 1)
					} else {
						match.events[i].counts = false
					}
				}
				for (let i = 0; i < match.users.length; i++) {
					match.users[i].isPlayer = false
				}
				this.match = match
				this.step = 2
			})
			.catch((err) => {
				console.log(err)
			})
		},
		choosePlayers() {
			this.players = this.match.users.filter(user => user.isPlayer == true)
			this.step = 3
		}
	},
	computed: {
		mappools() {
			return this.$store.state.mappools
		},
		searchDisabled() {
			return this.selectedMappool == null || this.matchId == null
		},
		choosePlayersDisabled() {
			return this.match.users.filter(user => user.isPlayer == true).length != 2
		}
	},
	watch: {
		selectedMappool(newMappool, oldMappool) {
			this.mappool = this.mappools.find((mappool) => {
				return mappool._id == newMappool
			})
		}
	}
}
</script>

<style lang="stylus" scoped>
.mappool-select
	max-width 300px
.match-id
	max-width 300px
.settings
	width 500px
	max-width 500px
</style>
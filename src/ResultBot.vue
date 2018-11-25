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
				v-layout(row)
					v-select.mr-2.beatmap-select(label="Beatmap" v-model="selectedBeatmap"  :items="mappool.slots" item-text="beatmap.beatmapset.title" item-value="beatmap.id")
					v-select.player-select(label="Player" v-model="selectedPlayer"  :items="players" item-text="username" item-value="id")
					v-btn(@click="addBan" color="success"  :disabled="addBanDisabled") Add
				v-data-table.ban-table(:items="bans" item-key="beatmap.beatmap.id" hide-actions)
					template(slot="headers" slot-scope="props")
						tr
							th(align="left" style="width:50px") Mods
							th(align="left") Beatmap
							th(align="left") Player
							th(align="right") Actions
					template(slot="items" slot-scope="props")
						tr
							td.text-xs-left {{ sortMods(props.item.beatmap.mods).join('') }}
							td.text-xs-left {{ props.item.beatmap.beatmap.beatmapset.title }}
							td.text-xs-left {{ props.item.player.username }}
							td.text-xs-right
								v-icon(small @click="deleteBan(props.item)") delete
				v-btn(@click="chooseBans" color="success") Continue
			v-stepper-content(step="4")
				h1 Picks
				v-layout(row v-for="game in games"  :key="game.id")
					v-checkbox.mr-2.pick-counts(v-model="game.counts"  :label="game.game.beatmap.beatmapset.title")
					v-select.player-select(label="Picked by" v-model="game.pickedBy"  :items="players" item-text="username"  item-value="id"  :disabled="!game.counts")
				v-btn(@click="choosePicks" color="success") Continue
			v-stepper-content(step="5")
				h1 Send Result
</template>

<script>
export default {
	name: 'ResultBot',
	data: () => ({
		step: 1,
		selectedMappool: null,
		selectedBeatmap: null,
		selectedPlayer: null,
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
		bans: [],
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
						match.events[i].counts = this.mappool.slots.some(slot => slot.beatmap.id == match.events[i].game.beatmap.id)
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
		},
		addBan() {
			this.bans.push({
				player: this.players.find(player => player.id == this.selectedPlayer),
				beatmap: this.mappool.slots.find(slot => slot.beatmap.id == this.selectedBeatmap)
			})
			this.selectedPlayer = null
			this.selectedBeatmap = null
		},
		deleteBan(item) {
			let index = this.bans.indexOf(item)
			this.bans.splice(index, 1)
		},
		chooseBans() {
			this.games = this.match.events
			for (let i = 0; i < this.games.length; i++) {
				this.games[i].pickedBy = null
			}
			this.step = 4
		},
		choosePicks() {
			for (let i = this.games.length - 1; i >= 0; i--) {
				if (!this.games[i].counts || this.games[i].pickedBy == null)
					this.games.splice(i, 1)
			}
			this.step = 5
		},
		sortMods(mods) {
			var modsCopy = mods.slice()
			modsCopy.sort(function(a, b) {
				if (a == 'HD')
					return -1
				if (a == 'DT')
					return 1
				if (b == 'HD')
					return 1
				if (b == 'DT')
					return -1
				return 0
			})
			return modsCopy
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
		},
		addBanDisabled() {
			if (this.selectedPlayer == null || this.selectedBeatmap == null)
				return true
			return this.bans.some(ban => ban.beatmap.beatmap.id == this.selectedBeatmap)
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
.mappool-select, .beatmap-select, .player-select
	max-width 300px
.match-id
	max-width 300px
.settings
	width 500px
	max-width 500px
.ban-table
	max-width 800px
.pick-counts
	max-width 500px
</style>
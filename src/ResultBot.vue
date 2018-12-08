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
				v-layout(row v-for="user in match.users"  :key="user.id")
					v-checkbox.mr-2.player-checkbox(v-model="user.isPlayer"  :label="user.username")
					v-checkbox(v-model="user.isRollWinner" label="Roll winner"  :disabled="!user.isPlayer")
				v-btn(@click="choosePlayers" color="success"  :disabled="choosePlayersDisabled") Continue
			v-stepper-content(step="3")
				h1 Bans
				v-layout(row)
					v-select.mr-2.beatmap-select(label="Beatmap" v-model="selectedBeatmap"  :items="mappool.slots" item-value="beatmap.id")
						template(slot="item" slot-scope="data")
							span [{{ getModNumber(data.item.beatmap.id) }}] {{ data.item.beatmap.beatmapset.title }}
						template(slot="selection" slot-scope="data")
							span [{{ getModNumber(data.item.beatmap.id) }}] {{ data.item.beatmap.beatmapset.title }}
					v-select.player-select(label="Player" v-model="selectedPlayer"  :items="players" item-text="username" item-value="id")
					v-btn(@click="addBan" color="success"  :disabled="addBanDisabled") Add
				v-data-table.ban-table(:items="bans" item-key="beatmap.beatmap.id" hide-actions)
					template(slot="headers" slot-scope="props")
						tr
							th(align="left" style="width:50px") Mod
							th(align="left") Beatmap
							th(align="left") Player
							th(align="right") Actions
					template(slot="items" slot-scope="props")
						tr
							td.text-xs-left {{ props.item.beatmap.mod }}
							td.text-xs-left {{ props.item.beatmap.beatmap.beatmapset.title }}
							td.text-xs-left {{ props.item.player.username }}
							td.text-xs-right
								v-icon(small @click="deleteBan(props.item)") delete
				v-btn(@click="chooseBans" color="success") Continue
			v-stepper-content(step="4")
				h1 Picks
				v-layout(row v-for="game in games"  :key="game.id")
					v-checkbox.mr-2.pick-counts(v-model="game.counts")
						template(slot="label")
							span [{{ getModNumber(game.game.beatmap.id) }}] {{ game.game.beatmap.beatmapset.title }}
					v-select.mr-2.player-select(label="Picked by" v-model="game.pickedBy"  :items="players" item-text="username"  item-value="id"  :disabled="!game.counts")
					v-checkbox.pick-counts(v-model="game.isTiebreaker" label="Tiebreaker")
				v-btn(@click="choosePicks" color="success") Continue
			v-stepper-content(step="5")
				h1 Send Result
				div(v-if="players.length >= 2")
					v-text-field.match-id(label="Title" v-model="title")
					div.mt-2
						strong {{ title }}
					div.mt-2(v-if="players[0].score > players[1].score")
						strong {{ players[0].username }} {{ players[0].score }}
						span  | {{ players[1].score }} {{ players[1].username }}
					div.mt-2(v-if="players[0].score < players[1].score")
						span {{ players[0].username }} {{ players[0].score }} |
						strong  {{ players[1].score }} {{ players[1].username }}
					div.mt-2
						div https://osu.ppy.sh/community/matches/{{ matchId }}
					div.mt-2
						div Winner of roll: {{ rollWinner.username }}
					div.mt-2
						strong Bans
					v-layout(row)
						.underline.mr-2 {{ players[0].username }}:
						.ban-mod {{ playerBans(0).map(ban => getModNumber(ban.beatmap.beatmap.id)).join('/') }}
					v-layout(row)
						.underline.mr-2 {{ players[1].username }}:
						.ban-mod {{ playerBans(1).map(ban => getModNumber(ban.beatmap.beatmap.id)).join('/') }}
					div.mt-2
						strong Picks
					v-layout(row)
						.underline.mr-2 {{ players[0].username }}:
						.pick-mod {{ playerPicks(0).map(pick => getModNumber(pick.game.beatmap.id)).join('/') }}
					v-layout(row)
						.underline.mr-2 {{ players[1].username }}:
						.pick-mod {{ playerPicks(1).map(pick => getModNumber(pick.game.beatmap.id)).join('/') }}
					div.mt-2(v-if="tiebreakers.length > 0")
						strong Tiebreakers
					v-layout(row v-for="pick in tiebreakers")
						.pick-mod {{ tiebreakers.map(pick => getModNumber(pick.game.beatmap.id)).join('/') }}
				v-btn(@click="sendResult" color="success"  :disabled="sendDisabled") Send
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
		games: [],
		sendDisabled: false,
		title: null
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
					match.users[i].isRollWinner = false
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
			for (let i = 0; i < this.players.length; i++) {
				this.players[i].score = 0
			}
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
			let nextPicker = this.players.indexOf(this.rollWinner)
			for (let i = 0; i < this.games.length; i++) {
				this.$set(this.games[i], 'pickedBy', this.players[nextPicker].id)
				nextPicker = nextPicker ? 0 : 1
				this.$set(this.games[i], 'isTiebreaker', false)
			}
			this.step = 4
		},
		choosePicks() {
			for (let i = this.games.length - 1; i >= 0; i--) {
				if ((!this.games[i].counts || this.games[i].pickedBy == null) && !this.games[i].isTiebreaker)
					this.games.splice(i, 1)
				else {
					let score0 = this.games[i].game.scores.find(score => score.user_id == this.players[0].id)
					let score1 = this.games[i].game.scores.find(score => score.user_id == this.players[1].id)
					if (score0 == null) {
						this.players[1].score++
					} else if (score1 == null) {
						this.players[0].score++
					} else if (score0.score > score1.score) {
						this.players[0].score++
					}
					else {
						this.players[1].score++
					}
					this.games[i].mod = this.mappool.slots.find(slot => slot.beatmap.id == this.games[i].game.beatmap.id).mod
				}
			}
			this.step = 5
		},
		playerBans(player) {
			return this.bans.filter(ban => ban.player.id == this.players[player].id)
		},
		playerPicks(player) {
			return this.games.filter(game => game.pickedBy == this.players[player].id && !game.isTiebreaker)
		},
		sendResult() {
			this.sendDisabled = true
			let description = '\n\n<https://osu.ppy.sh/community/matches/' + this.matchId + '>'
			if (this.players[0].score > this.players[1].score) {
				description = '**' + this.players[0].username + ' ' + this.players[0].score + '** | ' + this.players[1].score + ' ' + this.players[1].username + description
			} else {
				description = this.players[0].username + ' ' + this.players[0].score + ' | **' + this.players[1].score + ' ' + this.players[1].username + '**' + description
			}
			if (this.rollWinner) {
				description = description + '\n\nWinner of roll: ' + this.rollWinner.username
			}
			let embed = {
				username: 'Result Bot',
				avatar_url: 'https://cdn.discordapp.com/attachments/503559550855675904/506108924722675754/bot.png',
				embeds: [{
					color: 16711680,
					author: {
						name: this.title,
						icon_url: 'https://images-ext-2.discordapp.net/external/zJZT3pGPZl6avCRuQOOnL1_1vktR3ZiN5KZTKKRmAvk/https/cdn0.iconfinder.com/data/icons/fighting-1/258/brawl003-512.png'
					},
					description: description,
					fields: [
						{
							name: 'Bans',
							value: '__' + this.players[0].username + '__ ' + this.playerBans(0).map(ban => this.getModNumber(ban.beatmap.beatmap.id)).join('/') + '\n' + '__' + this.players[1].username + '__ ' + this.playerBans(1).map(ban => this.getModNumber(ban.beatmap.beatmap.id)).join('/')
						},
						{
							name: 'Picks',
							value: '__' + this.players[0].username + '__ ' + this.playerPicks(0).map(pick => this.getModNumber(pick.game.beatmap.id)).join('/') + '\n' + '__' + this.players[1].username + '__ ' + this.playerPicks(1).map(pick => this.getModNumber(pick.game.beatmap.id)).join('/')
						}
					]
				}]
			}
			if (this.tiebreakers.length > 0) {
				embed.embeds[0].fields.push({
					name: 'TB map',
					value: this.tiebreakers.map(pick => this.getModNumber(pick.game.beatmap.id)).join('/')
				})
			}
			this.axios.post('/api/resultmessage', { embed: embed })
			.then((result) => {
				console.log(result)
			})
			.catch((err) => {
				console.log(err)
			})
		},
		banSelectText(beatmap) {
			return beatmap.mod + ' ' + beatmap.beatmapset.title
		},
		getModNumber(beatmapId) {
			let modNumber = 0
			let lastMod = 'NM'
			for (let i = 0; i < this.mappool.slots.length; i++) {
				if (this.mappool.slots[i].mod != lastMod) {
					lastMod = this.mappool.slots[i].mod
					modNumber = 1
				} else {
					modNumber++
				}
				if (this.mappool.slots[i].beatmap.id == beatmapId) {
					break
				}
			}
			return lastMod + modNumber
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
		},
		tiebreakers() {
			return this.games.filter(game => game.isTiebreaker)
		},
		rollWinner() {
			return this.players.find(player => player.isRollWinner)
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
.player-checkbox
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
.underline
	text-decoration underline
</style>
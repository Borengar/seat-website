<template lang="pug">
v-layout(column)
	div
		v-layout(row)
			v-select.mr-2.mappool-select(label="Mappool" v-model="selectedMappool"  :items="mappools" item-text="name" item-value="_id")
			v-btn(flat icon @click="createMappool")
				v-icon add
			v-btn(flat icon @click="deleteMappool")
				v-icon delete
	div
		v-layout(row v-if="mappool._id")
			v-flex(lg6).mr-5
				v-layout(column)
					v-text-field.name-text-field(label="Name" v-model="mappool.name")
					v-data-table.elevation-1(:items="mappool.slots" item-key="beatmap.id" hide-actions)
						template(slot="headers" slot-scope="props")
							tr
								th(align="left") Mods
								th(align="left") Beatmap
								th(align="right") Actions
						template(slot="items" slot-scope="props")
							tr
								td.text-xs-left {{ sortMods(props.item.mods).join('') }}
								td.text-xs-left {{ props.item.beatmap.beatmapset.title }}
								td.text-xs-right.horizontal
									v-icon.mr-2(small @click="moveUp(props.item)"  :disabled="addVisible || bulkAddVisible || editVisible") keyboard_arrow_up
									v-icon.mr-2(small @click="moveDown(props.item)"  :disabled="addVisible || bulkAddVisible || editVisible") keyboard_arrow_down
									v-icon.mr-2(small @click="editSlot(props.item)"  :disabled="addVisible || bulkAddVisible || editVisible") edit
									v-icon(small @click="deleteSlot(props.item)"  :disabled="addVisible || bulkAddVisible || editVisible") delete
					v-layout(row)
						v-btn(@click="addSlot" color="success") Add beatmap
						v-btn(@click="addMultipleSlots" color="success") Bulk add
						v-btn(@click="saveMappool" color="success") Save
			v-flex(lg6)
				v-layout(column v-if="addVisible")
					h2 Add beatmap
					div
						v-layout(row)
							v-text-field(label="Beatmap ID" v-model="beatmapQuery" @keyup.enter="searchBeatmap")
							v-btn(@click="searchBeatmap" color="success") Search
					div(v-if="beatmap")
						beatmap-big(:beatmap="beatmap"  :mods="sortMods(mods)")
						v-layout(row)
							v-checkbox(v-model="mods" label="HD" value="HD")
							v-checkbox(v-model="mods" label="HR" value="HR")
							v-checkbox(v-model="mods" label="DT" value="DT")
							v-checkbox(v-model="mods" label="Freemod" value="Freemod")
							v-checkbox(v-model="mods" label="Tiebreaker" value="Tiebreaker")
					div(v-if="beatmapset")
						h3 Please choose a difficulty.
						v-radio-group(v-model="difficulty")
							v-radio(v-for="beatmap in beatmapset.beatmaps"  :key="beatmap.id"  :value="beatmap.id"  :label="`${beatmap.version} (${beatmap.difficulty_rating.toPrecision(2)}* CS${beatmap.cs} HP${beatmap.drain} OD${beatmap.accuracy} AR${beatmap.ar})`")
					div(v-if="beatmapsets.length")
						h3 Search result
						v-radio-group(v-model="chosenSet")
							v-radio(v-for="set in beatmapsets"  :key="set.id"  :value="set"  :label="`${set.artist} - ${set.title} (by ${set.creator})`")
					v-layout(row)
						v-btn(@click="cancel") Cancel
						v-btn(@click="addBeatmap" v-if="beatmap" color="success") Add
						v-btn(@click="chooseDifficulty" v-if="difficulty" color="success") Choose
						v-btn(@click="chooseSet" v-if="chosenSet" color="success") Choose
				v-layout(column v-if="bulkAddVisible")
					h2 Add multiple beatmaps
					v-textarea(label="Beatmaps" v-model="beatmapQuery")
					v-layout(row)
						v-btn(@click="cancel") Cancel
						v-btn(@click="startBulkAdd" color="success") Start
				v-layout(column v-if="editVisible")
					h2 Edit beatmap
					beatmap-big(:beatmap="beatmap"  :mods="sortMods(mods)")
					v-layout(row)
						v-checkbox(v-model="mods" label="HD" value="HD")
						v-checkbox(v-model="mods" label="HR" value="HR")
						v-checkbox(v-model="mods" label="DT" value="DT")
						v-checkbox(v-model="mods" label="Freemod" value="Freemod")
						v-checkbox(v-model="mods" label="Tiebreaker" value="Tiebreaker")
					v-layout(row)
						v-btn(@click="cancel") Cancel
						v-btn(@click="saveBeatmap" v-if="beatmap" color="success") Save
</template>

<script>
export default {
	name: 'Mappools',
	data: () => ({
		selectedMappool: null,
		mappool: {
			_id: null,
			name: null,
			slots: []
		},
		addVisible: false,
		bulkAddVisible: false,
		editVisible: false,
		beatmapQuery: null,
		bulkQueue: [],
		beatmap: null,
		mods: [],
		beatmapset: null,
		beatmapsets: [],
		difficulty: null,
		chosenSet: null
	}),
	computed: {
		mappools() {
			return this.$store.state.mappools
		}
	},
	methods: {
		createMappool() {
			this.axios.post('/api/mappools').
			then((result) => {
				return this.$store.dispatch('updateMappools')
			})
			.then((result) => {
				this.selectedMappool = this.mappools[this.mappools.length - 1]._id
			})
			.catch((err) => {
				console.log(err)
			})
		},
		deleteMappool() {

		},
		saveMappool() {
			this.axios.put('/api/mappools/' + this.mappool._id, {
				name: this.mappool.name,
				slots: this.mappool.slots
			})
			.then((result) => {

			})
			.catch((err) => {
				console.log(err)
			})
		},
		addSlot() {
			this.beatmapQuery = null
			this.beatmap = null
			this.beatmapset = null
			this.difficulty = null
			this.beatmapsets = []
			this.mods = []
			this.addVisible = true
			this.bulkAddVisible = false
			this.editVisible = false
		},
		addMultipleSlots() {
			this.addVisible = false
			this.bulkAddVisible = true
		},
		editSlot(slot) {
			this.beatmap = slot.beatmap
			this.mods = slot.mods.slice()
			this.addVisible = false
			this.bulkAddVisible = false
			this.editVisible = true
		},
		deleteSlot(slot) {
			let index = this.mappool.slots.indexOf(slot)
			this.mappool.slots.splice(index, 1)
		},
		searchBeatmap() {
			this.axios.get('/api/osubeatmap/' + this.beatmapQuery)
			.then((result) => {
				if (result.data)
					this.beatmap = result.data
				else {
					this.axios.get('/api/osubeatmapset/' + this.beatmapQuery)
					.then((result) => {
						if (result.data) {
							result.data.beatmaps.sort((a, b) => { return a.difficulty_rating - b.difficulty_rating })
							this.beatmapset = result.data
						}
						else {
							this.axios.get('/api/osubeatmapsetsearch/' + this.beatmapQuery)
							.then((result) => {
								this.beatmapsets = result.data
							})
							.catch((err) => {
								console.log(err)
							})
						}
					})
					.catch((err) => {
						console.log(err)
					})
				}
			})
			.catch((err) => {
				console.log(err)
			})
		},
		removeMods(mods, modsToRemove) {
			let modsChanged = false
			let modsCopy = mods.slice()
			for (let i = 0; i < modsToRemove.length; i++) {
				let index = modsCopy.indexOf(modsToRemove[i])
				if (index > -1) {
					modsCopy.splice(index, 1)
					modsChanged = true
				}
			}
			if (modsChanged)
				this.mods = modsCopy
		},
		cancel() {
			this.addVisible = false
			this.bulkAddVisible = false
			this.editVisible = false
		},
		addBeatmap() {
			this.mappool.slots.push({
				beatmap: this.beatmap,
				mods: this.mods
			})
			this.addVisible = false
		},
		chooseDifficulty() {
			this.beatmapQuery = this.difficulty
			this.axios.get('/api/osubeatmap/' + this.difficulty)
			.then((result) => {
				this.beatmap = result.data
				this.beatmapset = null
				this.difficulty = null
			})
			.catch((err) => {
				console.log(err)
			})
		},
		chooseSet() {
			this.beatmapset = this.chosenSet
			this.beatmapQuery = this.beatmapset.id.toString()
			this.chosenSet = null
			this.beatmapsets.splice(0, this.beatmapsets.length)
		},
		startBulkAdd() {
			this.bulkQueue = this.beatmapQuery.split('\n')
			this.bulkWorker(0)
		},
		bulkWorker(counter) {
			if (this.bulkQueue.length > 0) {
				var beatmapId = this.bulkQueue.shift()
				this.axios.get('/api/osubeatmap/' + beatmapId)
				.then((result) => {
					if (result.data) {
						var mods = []
						if (counter >= 6 && counter <= 7) mods = [ 'HD' ]
						if (counter >= 8 && counter <= 9) mods = [ 'HR' ]
						if (counter >= 10 && counter <= 11) mods = [ 'DT' ]
						if (counter >= 12 && counter <= 14) mods = [ 'Freemod' ]
						if (counter == 15) mods = [ 'Tiebreaker' ]
						this.mappool.slots.push({
							beatmap: result.data,
							mods: mods
						})
					}
					this.bulkWorker(counter + 1)
				})
				.catch((err) => {
					console.log(err)
					this.bulkWorker(counter + 1)
				})
			} else {
				this.cancel()
			}
		},
		moveUp(slot) {
			let index = this.mappool.slots.indexOf(slot)
			if (index > 0) {
				this.mappool.slots.splice(index, 1)
				this.mappool.slots.splice(index - 1, 0, slot)
			}
		},
		moveDown(slot) {
			let index = this.mappool.slots.indexOf(slot)
			if (index < this.mappool.slots.length - 1) {
				this.mappool.slots.splice(index, 1)
				this.mappool.slots.splice(index + 1, 0, slot)
			}
		},
		saveBeatmap() {
			let slot = this.mappool.slots.find((mappoolSlot) => {
				return mappoolSlot.beatmap == this.beatmap
			})
			slot.mods = this.mods
			this.cancel()
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
	watch: {
		selectedMappool(newMappool, oldMappool) {
			this.mappool = this.mappools.find((mappool) => {
				return mappool._id == newMappool
			})
		},
		mods(newMods, oldMods) {
			if (newMods.length) {
				switch (newMods[newMods.length - 1]) {
					case 'HD':
					case 'HR':
					case 'DT':
						this.removeMods(newMods, [ 'Freemod', 'Tiebreaker' ])
						break
					case 'Freemod':
						this.removeMods(newMods, [ 'HD', 'HR', 'DT', 'Tiebreaker' ])
						break
					case 'Tiebreaker':
						this.removeMods(newMods, [ 'HD', 'HR', 'DT', 'Freemod' ])
						break
				}
			}
		},
		beatmapQuery(newQuery, oldQuery) {
			if (newQuery) {
				newQuery = newQuery.replace(/https:\/\/osu\.ppy\.sh\/b\//, '')
				newQuery = newQuery.replace(/https:\/\/osu\.ppy\.sh\/beatmapsets\/[^#]+osu\//, '')
				this.beatmapQuery = newQuery
			}
		}
	}
}
</script>

<style lang="stylus" scoped>
.mappool-select
	max-width 300px
.name-text-field
	max-width 300px
</style>
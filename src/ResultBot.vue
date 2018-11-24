<template lang="pug">
v-layout(column)
	div
		v-layout(row)
			v-select.mr-2.mappool-select(label="Mappool" v-model="selectedMappool"  :items="mappools" item-text="name" item-value="_id")
			v-text-field.mr-2.match-id(label="Match ID" v-model="matchId")
			v-btn(@click="searchMatch" color="success") Search
</template>

<script>
export default {
	name: 'ResultBot',
	data: () => ({
		selectedMappool: null,
		matchId: null,
		match: null,
		mappool: {
			_id: null,
			name: null,
			slots: []
		}
	}),
	methods: {
		searchMatch() {
			this.axios.get('/api/osumatch/' + this.matchId)
			.then((result) => {
				this.match = result.data
			})
			.catch((err) => {
				console.log(err)
			})
		}
	},
	computed: {
		mappools() {
			return this.$store.state.mappools
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
</style>
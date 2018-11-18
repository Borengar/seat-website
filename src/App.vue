<template lang="pug">
v-app
	v-navigation-drawer(app persistent v-if="loggedIn")
		v-toolbar(color="light-primary")
			v-list
				v-list-tile
					v-list-tile-title.title Navigation
		v-divider
		v-list
			v-list-tile(to="/resultbot")
				v-list-tile-title Result Bot
			v-list-tile(to="/mappools")
				v-list-tile-title Mappools
	v-toolbar(app color="light-primary")
		v-toolbar-title o!SEAT 2
		v-spacer
		discord-profile(:profile="discordProfile" v-if="discordProfile.id")
	v-content(grid-list-lg)
		v-container(fluid fill-height)
			router-view
</template>

<script>
export default {
	name: 'app',
	computed:  {
		loggedIn() {
			return this.$store.state.user.token != null
		},
		discordProfile() {
			return this.$store.state.user.discord
		}
	},
	mounted() {
		var token = localStorage.getItem('token')
		if (token) {
			this.axios.defaults.headers.common['Authorization'] = token
			this.$store.state.user.token = token
			this.$store.dispatch('init')
		}
	}
}
</script>

<style lang="stylus" scoped>

</style>
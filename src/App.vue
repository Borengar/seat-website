<template lang="pug">
v-app
	v-toolbar(app color="light-primary")
		v-toolbar-side-icon(v-if="loggedIn")
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
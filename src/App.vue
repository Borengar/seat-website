<template lang="pug">
v-app
	v-toolbar(app color="light-primary")
		v-toolbar-side-icon
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
			return this.axios.defaults.headers.common.hasOwnProperty('Authorization')
		},
		discordProfile() {
			return this.$store.state.user.discord
		}
	}
}
</script>

<style lang="stylus" scoped>

</style>
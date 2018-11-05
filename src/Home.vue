<template lang="pug">
v-layout(column align-center)
	.header.mt-5 Welcome to o!SEAT 2
	v-layout(column align-center v-if="!loggedIn")
		.login-info You must be logged in to access this page
		v-btn.md-raised.mt-5(color="discord" large dark @click="login") Authenticate with Discord
</template>

<script>
export default {
	name: 'Home',
	computed:  {
		loggedIn() {
			return this.axios.defaults.headers.common.hasOwnProperty('Authorization')
		}
	},
	methods: {
		login() {
			window.open('https://discordapp.com/oauth2/authorize?client_id=506094673463345182&scope=identify&redirect_uri=' + encodeURI('http://localhost/discordlogin') + '&response_type=token', '_self')
		}
	}
}
</script>

<style lang="stylus" scoped>
.header
	font-size 36px
	font-weight bold
.login-info
	font-size 20px
</style>
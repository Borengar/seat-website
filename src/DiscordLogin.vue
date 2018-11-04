<template lang="pug">
v-layout(column align-center)
	div(v-if="accessDenied")
		h1 Please authorize Discord to access this page.
</template>

<script>
export default {
	name: 'DiscordLogin',
	data: () => ({
		accessDenied: false
	}),
	created() {
		var token = null
		var error = null
		this.$route.hash.substring(1).split('&').forEach((element) => {
			var splits = element.split('=')
			if (splits[0] == 'access_token')
				token = splits[1]
			if (splits[0] == 'error')
				error = splits[1]
		})
		if (token == null || error == 'access_denied') {
			this.accessDenied = true
			return
		}
		var self = this
		this.axios.post('/api/discordlogin', {
			token: token
		})
		.then((response) => {
			if (response.status == 200) {
				//self.$router.push('/registration')
			}
		})
		.catch((err) => {
			console.log(err)
		})
	}
}
</script>

<style lang="styylus" scoped>

</style>
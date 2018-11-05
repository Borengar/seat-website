<template lang="pug">
v-layout.wrapper(row)
	.avatar.mr-2.mt-2(:style='avatarUrl')
	.username {{ profile.username }}\#{{ profile.discriminator }}
</template>

<script>
export default {
	name: 'DiscordProfile',
	props: {
		profile: Object
	},
	computed: {
		avatarUrl: function() {
			if (this.profile.id)
				return { 'background-image': 'url(https://cdn.discordapp.com/avatars/' + this.profile.id + '/' + this.profile.avatar + '.gif?size=256)' }
			return ''
		}
	}
}
</script>

<style lang="stylus" scoped>
.wrapper
	max-width 300px
	height 64px
.avatar
	width 50px
	height 50px
	border-radius 50%
	background-size 50px 50px
.username
	line-height 64px
	font-size 20px
</style>
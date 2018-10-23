<template lang="pug">
v-layout(column align-center)
	.header.mt-5 Welcome to o!SEAT 2
	.login-info You must be logged in to access this page
	v-btn.mt-5(color="discord" large dark) Authenticate with Discord
</template>

<script>
export default {
	name: 'Home'
}
</script>

<style lang="stylus" scoped>
.header
	font-size 36px
	font-weight bold
.login-info
	font-size 20px
</style>
<template lang="pug">
v-app
	v-toolbar(app color="light-primary")
		v-toolbar-side-icon
		v-toolbar-title o!SEAT 2
	v-content(grid-list-lg)
		v-container(fluid fill-height)
			router-view
</template>

<script>
export default {
	name: 'app'
}
</script>

<style lang="stylus" scoped>

</style>
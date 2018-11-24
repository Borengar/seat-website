// Vue
import Vue from 'vue'
import VueRouter from 'vue-router'

// Material Design
import Vuetify from 'vuetify'
import 'vuetify/dist/vuetify.min.css'

// Source pages
import App from './App.vue'
import Home from './Home.vue'
import DiscordLogin from './DiscordLogin.vue'
import Mappools from './Mappools.vue'
import ResultBot from './ResultBot.vue'

// Custom components
import DiscordProfile from './DiscordProfile.vue'
import BeatmapBig from './BeatmapBig.vue'

// Other stuff
import axios from 'axios'
import VueAxios from 'vue-axios'
import Vuex from 'vuex'
import { mapState } from 'vuex'

// Theming
import theme from './theme.js'
import './main.styl'

Vue.use(VueRouter)
Vue.use(Vuetify, {
	theme: theme
})
Vue.component('discord-profile', DiscordProfile)
Vue.component('beatmap-big', BeatmapBig)
Vue.use(VueAxios, axios)
Vue.use(Vuex)

const router = new VueRouter({
	mode: 'history',
	base: __dirname,
	routes: [
		{ path: '/', redirect: '/home' },
		{ path: '/home', component: Home, name: 'Home' },
		{ path: '/discordlogin', component: DiscordLogin, name: 'Discord Login' },
		{ path: '/mappools', component: Mappools, name: 'Mappools' },
		{ path: '/resultbot', component: ResultBot, name: 'Result Bot' }
	]
})

const store = new Vuex.Store({
	state: {
		user: {
			discord: { username: null, discriminator: null, id: null, avatar: null },
			token: null
		},
		mappools: []
	},
	mutations: {
		updateUser(state, payload) {
			state.user.discord = payload.profile.discord
		},
		updateMappools(state, payload) {
			state.mappools = payload.mappools
		}
	},
	actions: {
		init({ commit }) {
			this.dispatch('updateUser')
			this.dispatch('updateMappools')
		},
		updateUser({ commit }) {
			axios.get('/api/user')
			.then((response) => {
				commit('updateUser', { profile: response.data })
			})
			.catch((err) => {
				console.log(err)
			})
		},
		updateMappools({ commit }) {
			axios.get('/api/mappools')
			.then((response) => {
				commit('updateMappools', { mappools: response.data })
			})
			.catch((err) => {
				console.log(err)
			})
		}
	}
})

new Vue({
	router,
	store,
	mounted: function() {

	},
	components: { App },
	template: `
		<App/>
	`
}).$mount('#app')
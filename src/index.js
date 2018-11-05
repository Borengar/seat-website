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

// Custom components
import DiscordProfile from './DiscordProfile.vue'

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
Vue.use(VueAxios, axios)
Vue.use(Vuex)

const router = new VueRouter({
	mode: 'history',
	base: __dirname,
	routes: [
		{ path: '/', redirect: '/home' },
		{ path: '/home', component: Home, name: 'Home' },
		{ path: '/discordlogin', component: DiscordLogin, name: 'Discord Login' }
	]
})

const store = new Vuex.Store({
	state: {
		user: {
			discord: { username: null, discriminator: null, id: null, avatar: null }
		}
	},
	mutations: {
		updateUser(state, payload) {
			state.user.discord = payload.profile.discord
		}
	},
	actions: {
		init({ commit }) {
			this.dispatch('updateUser')
		},
		updateUser({ commit }) {
			axios.get('/api/user')
			.then((response) => {
				commit('updateUser', { profile: response.data })
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
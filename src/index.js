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

	},
	actions: {
		init({ commit }) {

		}
	}
})

new Vue({
	router,
	store,
	mounted: function() {
		store.dispatch('init')
	},
	components: { App },
	template: `
		<App/>
	`
}).$mount('#app')
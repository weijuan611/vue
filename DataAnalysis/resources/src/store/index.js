import Vuex from "vuex"
import Vue from 'vue'
import NavTabs from './navTabs'
import PagePassValue from './pagePassValue'

Vue.use(Vuex)

export default new Vuex.Store({
    modules: {
        navTabs: NavTabs,
        pagePassValue: PagePassValue
    }
})

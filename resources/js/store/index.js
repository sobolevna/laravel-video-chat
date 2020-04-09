import Vue from 'vue'
import Vuex from 'vuex'
Vue.use(Vuex);
import createPersistedState from 'vuex-persistedstate'

const store = new Vuex.Store({
    modules: {
        videochat: require('./videochat').default,
    },
    plugins: [
        createPersistedState({ storage: window.sessionStorage })
    ]
});

export default store;

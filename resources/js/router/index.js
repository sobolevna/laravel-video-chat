import Vue from 'vue';
import VueRouter from 'vue-router';
Vue.use(VueRouter);

import laravelVideoChat from './laravel-video-chat.js';

const routes = [
    ...laravelVideoChat
]

// 3. Создаём экземпляр маршрутизатора и передаём маршруты в опции `routes`
// Вы можете передавать и дополнительные опции, но пока не будем усложнять.
const router = new VueRouter({
    mode: 'history',
    routes 
})

export default router;
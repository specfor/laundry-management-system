import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from './router'

import { OhVueIcon, addIcons } from "oh-vue-icons";
import { HiMenu, MdCloseRound } from "oh-vue-icons/icons";

addIcons(HiMenu, MdCloseRound);


createApp(App)
    .use(router)
    .component("v-icon", OhVueIcon)
    .mount('#app')

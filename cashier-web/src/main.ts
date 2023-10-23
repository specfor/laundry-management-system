import './assets/main.css'
import 'element-plus/theme-chalk/dark/css-vars.css'
import 'vue-virtual-scroller/dist/vue-virtual-scroller.css'

// @ts-ignore (IDE Complains here for no reason)
import en from './locales/en.json';
// @ts-ignore (IDE Complains here for no reason)
import si from './locales/si.json';


import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { createI18n } from 'vue-i18n'

const app = createApp(App)
const i18n = createI18n({
    availableLocales: ['en', 'si'],
    locale: getStartingLocale(),
    messages: {
        en,
        si
    }
})

app.use(router)
app.use(i18n)

app.mount('#app')

function getStartingLocale() {
    const prevLocale = localStorage.getItem('app-locale')
    return prevLocale ? prevLocale : "en";
}
import './assets/main.css'

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { createI18n } from 'vue-i18n'

const app = createApp(App)
const i18n = createI18n({
    availableLocales: ['en', 'si']
})

app.use(router)
app.use(i18n)

app.mount('#app')

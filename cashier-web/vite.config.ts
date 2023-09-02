import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'
import Icons from 'unplugin-icons/vite'
import ElementPlus from 'unplugin-element-plus/vite'
import IconsResolver from 'unplugin-icons/resolver'
import Components from 'unplugin-vue-components/vite'
import VueI18nPlugin from '@intlify/unplugin-vue-i18n/vite'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    ElementPlus({}),
    VueI18nPlugin({}),
    Components({
      resolvers: [
        IconsResolver({
          prefix: 'icon',
          enabledCollections: ['mdi'],
        }),
      ],
    }),
    Icons({
      compiler: 'vue3'
    }),
    vueJsx()
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  build: {
    outDir: "../api/public/cashier",
  },
  base: '/cashier'
})

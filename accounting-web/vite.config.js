/// <reference types="vitest" />
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import ElementPlus from 'unplugin-element-plus/vite'
import vueJsx from '@vitejs/plugin-vue-jsx'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    vue(), ElementPlus(),
    vueJsx()
  ],
  test: {
    include: ['**/*.test.*', '**/*.spec.*'],
    environment: "happy-dom",
    globals: true
  }
})

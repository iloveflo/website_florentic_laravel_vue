import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig({
  base: '/',
  plugins: [
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
  build: {
    outDir: 'frontend', // Thư mục output khi build
  },
  
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:8000', // địa chỉ Laravel
        changeOrigin: true,
        secure: false,
      },
            
      '/uploads': {
        target: 'http://localhost:8000', // địa chỉ Laravel
        changeOrigin: true,
        secure: false,
      },

      '/auth': {
        target: 'http://localhost:8000', // địa chỉ Laravel
        changeOrigin: true,
        secure: false,
      }
    }
  },
})

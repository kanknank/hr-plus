import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  server: {
    proxy: {
      '/api': {
    target: 'http://jsonplaceholder.typicode.com',
    changeOrigin: true,
    rewrite: (path) => path.replace(/^\/api/, ''),
  },
    }
  },
    plugins: [
      vue({
        
      }),
    ]
  })

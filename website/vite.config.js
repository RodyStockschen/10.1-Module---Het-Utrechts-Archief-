import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig({
  base: '/dist/',
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'src/js/main.js'),
        cms: resolve(__dirname, 'src/js/cms.js'),
      },
    },
  },
  server: {
    port: 5173,
    strictPort: true,
    origin: 'http://localhost:5173',
  },
})

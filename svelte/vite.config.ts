import { defineConfig } from 'vite';
import { svelte } from '@sveltejs/vite-plugin-svelte';

export default defineConfig({
  plugins: [svelte()],
  build: {
    outDir: '../assets',
    emptyOutDir: false,
    lib: {
      entry: 'src/main.ts',
      name: 'components',
      fileName: () => 'js/components.js',
      formats: ['iife']
    },
    rollupOptions: {
      output: {
        assetFileNames: (assetInfo) => {
          if (assetInfo.name?.endsWith('.css')) {
            return 'css/svelte.css';
          }
          return 'js/[name][extname]';
        }
      }
    }
  }
});

import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from "@tailwindcss/vite";
import path from 'path';

export default defineConfig({
  plugins: [
    tailwindcss(),
    react()
  ],
  build: {
    outDir: '../marketplace_backend/public/react',
    emptyOutDir: true,
  },
  //  base: '/react/', // Raha mampiasa sub-directory ao amin'ny public
  resolve: {
    alias: {
      hooks: path.resolve(__dirname, './src/hooks'),
      utils: path.resolve(__dirname, './src/utils'),
      components: path.resolve(__dirname, './src/components'),
    },
  },
})
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

// frontend/ を Vite のルートにし、ビルド成果物は Laravel の public/ に出力する
// （nginx がそのまま静的配信できるようにするため）。
export default defineConfig({
  root: 'frontend',
  plugins: [react()],
  build: {
    outDir: '../public',
    emptyOutDir: false,
    assetsDir: 'assets',
  },
  server: {
    host: true,
    port: 5173,
    strictPort: true,
  },
});

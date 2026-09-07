import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  resolve: {
    // AreaListView.vue / AreaFormView.vue / AreaCard.vue が `@/...` でimportしているため必要。
    // これが無いとArea画面のビルド・テストが失敗する（Idea機能とは無関係の既存バグ）。
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  test: {
    environment: 'jsdom',
    globals: true,
  },
})

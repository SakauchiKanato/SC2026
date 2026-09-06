<script setup>
import { ref } from 'vue'
import '../../assets/idea-theme.css'
import IdeaForm from '../../components/Idea/IdeaForm.vue'
import { useIdeaStore } from '../../store/idea'

const { errorMessage, registerIdea } = useIdeaStore()

const formRef = ref(null)
const isSubmitting = ref(false)
const successMessage = ref('')

async function handleSubmit(payload) {
  isSubmitting.value = true
  successMessage.value = ''
  try {
    await registerIdea(payload)
    successMessage.value = 'アイデアを登録しました。'
    formRef.value?.resetForm()
    // NOTE: 一覧画面へのrouteが定義され次第、ここでrouter.push()して
    //       一覧画面へ遷移させる想定（現時点ではrouter未設定のため未実装）。
  } catch {
    // エラー内容はstoreのerrorMessageで表示するため、ここでは何もしない
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <section class="idea-form-view">
    <header class="idea-page-header">
      <h1>アイデアを登録する</h1>
      <p class="idea-page-description">
        取り組んだ地域活性化のアイデアと、成功・失敗につながった理由を記録してください。
        似た土地で挑戦する人の道しるべになります。
      </p>
    </header>

    <p v-if="successMessage" class="idea-banner idea-banner--success" role="status">
      {{ successMessage }}
    </p>
    <p v-if="errorMessage" class="idea-banner idea-banner--error" role="alert">
      {{ errorMessage }}
    </p>

    <IdeaForm ref="formRef" @submit="handleSubmit" />

    <p v-if="isSubmitting" class="idea-loading-indicator">登録中です…</p>
  </section>
</template>

<style scoped>
.idea-form-view {
  max-width: 640px;
  margin: 0 auto;
  padding: var(--idea-spacing-lg) var(--idea-spacing-md);
}
</style>
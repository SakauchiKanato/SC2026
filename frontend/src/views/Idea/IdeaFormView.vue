<script setup>
import { ref, onMounted } from 'vue'
import '../../assets/idea-theme.css'
import IdeaForm from '../../components/Idea/IdeaForm.vue'
import { useIdeaStore } from '../../store/idea'
import { fetchArea } from '@/api/area'

const props = defineProps({
  areaId: {
    type: [String, Number],
    required: true,
  },
})

const { errorMessage, registerIdea } = useIdeaStore()

const area = ref(null)
const loadError = ref('')

const formRef = ref(null)
const isSubmitting = ref(false)
const successMessage = ref('')

async function loadArea() {
  try {
    area.value = await fetchArea(props.areaId)
  } catch (error) {
    loadError.value = '地域データの取得に失敗しました'
  }
}

async function handleSubmit(payload) {
  isSubmitting.value = true
  successMessage.value = ''
  try {
    await registerIdea(props.areaId, payload)
    successMessage.value = 'アイデアを登録しました。'
    formRef.value?.resetForm()
  } catch {
    // エラー内容はstoreのerrorMessageで表示するため、ここでは何もしない
  } finally {
    isSubmitting.value = false
  }
}

onMounted(loadArea)
</script>

<template>
  <section class="idea-form-view">
    <header class="idea-page-header">
      <h1>アイデアを登録する</h1>
      <p class="idea-page-description">
        <template v-if="area">{{ area.name }}に向けて、</template>
        取り組みたい地域活性化のアイデアと、成功・失敗につながった理由を記録してください。
        似た土地で挑戦する人の道しるべになります。
      </p>
    </header>

    <p v-if="loadError" class="idea-banner idea-banner--error">{{ loadError }}</p>
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

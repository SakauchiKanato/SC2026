<script setup>
import { reactive } from 'vue'

const MAX_TITLE_LENGTH = 60
const MAX_CONTENT_LENGTH = 1000

const emit = defineEmits(['submit'])

// NOTE: 「結果（成功／失敗）」「理由」は発案者の自己申告を廃止し、
// 企業・自治体側が後からアイデアを評価する仕様に変更したため、
// 登録フォームからは削除している（IdeaDetailView.vue側に評価UIを実装）。
const form = reactive({
  title: '',
  content: '',
})

const errors = reactive({
  title: '',
  content: '',
})

function validate() {
  errors.title = form.title.trim()
    ? form.title.length > MAX_TITLE_LENGTH
      ? `タイトルは${MAX_TITLE_LENGTH}文字以内で入力してください`
      : ''
    : 'タイトルを入力してください'

  errors.content = form.content.trim()
    ? form.content.length > MAX_CONTENT_LENGTH
      ? `内容は${MAX_CONTENT_LENGTH}文字以内で入力してください`
      : ''
    : 'アイデアの内容を入力してください'

  return !errors.title && !errors.content
}

function handleSubmit() {
  if (!validate()) return
  emit('submit', { ...form })
}

function resetForm() {
  form.title = ''
  form.content = ''
  errors.title = ''
  errors.content = ''
}

defineExpose({ resetForm })
</script>

<template>
  <form class="idea-form" novalidate @submit.prevent="handleSubmit">
    <div class="idea-form__field">
      <label for="idea-title">タイトル</label>
      <input
        id="idea-title"
        v-model="form.title"
        type="text"
        :maxlength="MAX_TITLE_LENGTH"
        placeholder="例）廃校を活用した週末マルシェ"
        :aria-invalid="Boolean(errors.title)"
      />
      <p v-if="errors.title" class="idea-form__error" role="alert">{{ errors.title }}</p>
    </div>

    <div class="idea-form__field">
      <label for="idea-content">アイデアの内容</label>
      <textarea
        id="idea-content"
        v-model="form.content"
        rows="5"
        :maxlength="MAX_CONTENT_LENGTH"
        placeholder="どんな取り組みかを具体的に書いてください"
        :aria-invalid="Boolean(errors.content)"
      ></textarea>
      <p class="idea-form__char-count">{{ form.content.length }} / {{ MAX_CONTENT_LENGTH }}</p>
      <p v-if="errors.content" class="idea-form__error" role="alert">{{ errors.content }}</p>
    </div>

    <button type="submit" class="idea-button">アイデアを登録する</button>
  </form>
</template>

<style scoped>
.idea-form {
  display: flex;
  flex-direction: column;
  gap: var(--idea-spacing-md);
  max-width: 560px;
}

.idea-form__field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.idea-form__field label,
.idea-form__label {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--idea-color-text);
}

.idea-form__field input[type='text'],
.idea-form__field textarea {
  border: 1px solid var(--idea-color-border);
  border-radius: var(--idea-radius);
  padding: 10px 12px;
  font-size: 0.95rem;
  font-family: inherit;
  color: var(--idea-color-text);
  background: var(--idea-color-surface);
}

.idea-form__field input[type='text']:focus,
.idea-form__field textarea:focus {
  outline: 2px solid var(--idea-color-accent);
  outline-offset: 1px;
}

.idea-form__field input[aria-invalid='true'],
.idea-form__field textarea[aria-invalid='true'] {
  border-color: var(--idea-color-danger);
}

.idea-form__char-count {
  align-self: flex-end;
  margin: 0;
  font-size: 0.75rem;
  color: var(--idea-color-text-muted);
}

.idea-form__error {
  margin: 0;
  font-size: 0.8rem;
  color: var(--idea-color-danger);
}
</style>

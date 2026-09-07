<script setup>
import { reactive } from 'vue'

const emit = defineEmits(['submit'])

const form = reactive({
  email: '',
  password: '',
})

const errors = reactive({
  email: '',
  password: '',
})

function validate() {
  // ログイン画面ではメールアドレスの存在有無を推測されないよう、
  // クライアント側では「未入力かどうか」のみをチェックする（形式チェックはしない）。
  errors.email = form.email.trim() ? '' : 'メールアドレスを入力してください'
  errors.password = form.password ? '' : 'パスワードを入力してください'

  return !errors.email && !errors.password
}

function handleSubmit() {
  if (!validate()) return
  emit('submit', {
    email: form.email.trim(),
    password: form.password,
  })
}
</script>

<template>
  <form class="idea-form" novalidate @submit.prevent="handleSubmit">
    <div class="idea-form__field">
      <label for="login-email">メールアドレス</label>
      <input
        id="login-email"
        v-model="form.email"
        type="email"
        autocomplete="email"
        placeholder="例）taro@example.com"
        :aria-invalid="Boolean(errors.email)"
      />
      <p v-if="errors.email" class="idea-form__error" role="alert">{{ errors.email }}</p>
    </div>

    <div class="idea-form__field">
      <label for="login-password">パスワード</label>
      <input
        id="login-password"
        v-model="form.password"
        type="password"
        autocomplete="current-password"
        :aria-invalid="Boolean(errors.password)"
      />
      <p v-if="errors.password" class="idea-form__error" role="alert">{{ errors.password }}</p>
    </div>

    <button type="submit" class="idea-button">ログインする</button>
  </form>
</template>

<style scoped>
/* IdeaForm.vueと同じ入力欄スタイル（コンポーネントごとにscopedなCSSを持つ既存の方針を踏襲） */
.idea-form {
  display: flex;
  flex-direction: column;
  gap: var(--idea-spacing-md);
  max-width: 420px;
}

.idea-form__field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.idea-form__field label {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--idea-color-text);
}

.idea-form__field input {
  border: 1px solid var(--idea-color-border);
  border-radius: var(--idea-radius);
  padding: 10px 12px;
  font-size: 0.95rem;
  font-family: inherit;
  color: var(--idea-color-text);
  background: var(--idea-color-surface);
}

.idea-form__field input:focus {
  outline: 2px solid var(--idea-color-accent);
  outline-offset: 1px;
}

.idea-form__field input[aria-invalid='true'] {
  border-color: var(--idea-color-danger);
}

.idea-form__error {
  margin: 0;
  font-size: 0.8rem;
  color: var(--idea-color-danger);
}
</style>

<script setup>
import { computed, reactive } from 'vue'

const props = defineProps({
  // 'user'（発案者ログイン） | 'company'（企業・自治体ログイン）
  // 呼び出し元（LoginView）がどちらのログインページかを渡す
  expectedRole: {
    type: String,
    default: null,
  },
})

const emit = defineEmits(['submit'])

const form = reactive({
  email: '',
  password: '',
})

const errors = reactive({
  email: '',
  password: '',
})

const emailPlaceholder = computed(() =>
  props.expectedRole === 'company' ? 'example@city-shibuya.jp' : 'example@machitane.jp',
)

// ボタンの活性/非活性表示用。ログイン画面ではメールアドレスの存在有無を推測されないよう、
// ここでも形式チェックはせず「未入力かどうか」のみで判定する（validate()と同じ方針）。
const canSubmit = computed(() => Boolean(form.email.trim()) && Boolean(form.password))

function validate() {
  errors.email = form.email.trim() ? '' : 'メールアドレスを入力してください'
  errors.password = form.password ? '' : 'パスワードを入力してください'

  return !errors.email && !errors.password
}

function handleSubmit() {
  if (!validate()) return
  emit('submit', {
    email: form.email.trim(),
    password: form.password,
    role: props.expectedRole,
  })
}
</script>

<template>
  <form class="mt-auth-card" novalidate @submit.prevent="handleSubmit">
    <div class="mt-auth-card__field">
      <label for="login-email">メールアドレス</label>
      <input
        id="login-email"
        v-model="form.email"
        type="email"
        autocomplete="email"
        :placeholder="emailPlaceholder"
        :aria-invalid="Boolean(errors.email)"
      />
      <p v-if="errors.email" class="mt-auth-card__error" role="alert">{{ errors.email }}</p>
    </div>

    <div class="mt-auth-card__field">
      <label for="login-password">パスワード</label>
      <input
        id="login-password"
        v-model="form.password"
        type="password"
        autocomplete="current-password"
        placeholder="パスワードを入力"
        :aria-invalid="Boolean(errors.password)"
      />
      <p v-if="errors.password" class="mt-auth-card__error" role="alert">{{ errors.password }}</p>
    </div>

    <button type="submit" class="mt-auth-card__submit" :disabled="!canSubmit">ログイン</button>
  </form>
</template>

<style scoped>
.mt-auth-card {
  display: flex;
  flex-direction: column;
  max-width: 440px;
  margin: 0 auto;
  padding: 36px;
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: 22px;
  box-shadow: var(--shadow-sm);
}

.mt-auth-card__field {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 20px;
}

.mt-auth-card__field label {
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--ink);
}

.mt-auth-card__field input {
  width: 100%;
  padding: 13px 16px;
  border: 1px solid var(--line);
  border-radius: 12px;
  font-size: 0.95rem;
  font-family: inherit;
  color: var(--ink);
  background: var(--bg);
}

.mt-auth-card__field input:focus {
  outline: none;
  border-color: var(--brand-500);
  box-shadow: 0 0 0 3px oklch(56% 0.1 146 / 0.14);
}

.mt-auth-card__field input[aria-invalid='true'] {
  border-color: var(--error);
}

.mt-auth-card__error {
  margin: 0;
  font-size: 0.8rem;
  color: var(--error);
}

.mt-auth-card__submit {
  width: 100%;
  padding: 15px;
  margin-top: 4px;
  border: none;
  border-radius: var(--mt-radius-pill);
  font-size: 0.95rem;
  font-weight: 700;
  background: var(--brand-700);
  color: #ffffff;
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.mt-auth-card__submit:not(:disabled):hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.mt-auth-card__submit:disabled {
  background: var(--line-soft);
  color: var(--ink-faint);
  cursor: not-allowed;
}
</style>

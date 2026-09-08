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

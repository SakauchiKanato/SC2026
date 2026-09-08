<script setup>
import { computed, reactive, ref } from 'vue'

const MAX_NAME_LENGTH = 100
const MIN_PASSWORD_LENGTH = 8
const MAX_PASSWORD_LENGTH = 72 // bcryptの仕様上72バイトを超える部分は無視されるため(backendと合わせる)
const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

const props = defineProps({
  // 発案者ログイン/企業・自治体ログインどちらの画面から新規登録に来たかに応じて、
  // 見出し・メールプレースホルダーを出し分ける。ロールは新デザインのログイン画面と
  // 同様、画面（ルート）ごとに固定とし、フォーム上で選び直せるようにはしていない。
  defaultRole: {
    type: String,
    default: 'user',
  },
})

const emit = defineEmits(['submit'])

const emailPlaceholder = computed(() =>
  props.defaultRole === 'company' ? 'example@city-shibuya.jp' : 'example@machitane.jp',
)

const form = reactive({
  name: '',
  email: '',
  password: '',
  passwordConfirmation: '',
})

const errors = reactive({
  name: '',
  email: '',
  password: '',
  passwordConfirmation: '',
})

// 利用規約への同意チェック。バックエンドには同意有無を記録する項目が無いため、
// UI上で送信を止めるためだけに使い、送信データには含めない。
const agreed = ref(false)

const canSubmit = computed(() => {
  const validEmail = EMAIL_PATTERN.test(form.email.trim())
  const validPassword =
    form.password.length >= MIN_PASSWORD_LENGTH && form.password.length <= MAX_PASSWORD_LENGTH
  const passwordsMatch =
    form.passwordConfirmation.length > 0 && form.passwordConfirmation === form.password
  return Boolean(form.name.trim()) && validEmail && validPassword && passwordsMatch && agreed.value
})

function validate() {
  errors.name = form.name.trim()
    ? form.name.length > MAX_NAME_LENGTH
      ? `お名前は${MAX_NAME_LENGTH}文字以内で入力してください`
      : ''
    : 'お名前を入力してください'

  errors.email = form.email.trim()
    ? EMAIL_PATTERN.test(form.email.trim())
      ? ''
      : 'メールアドレスの形式が正しくありません'
    : 'メールアドレスを入力してください'

  errors.password = form.password
    ? form.password.length < MIN_PASSWORD_LENGTH
      ? `パスワードは${MIN_PASSWORD_LENGTH}文字以上で入力してください`
      : form.password.length > MAX_PASSWORD_LENGTH
        ? `パスワードは${MAX_PASSWORD_LENGTH}文字以内で入力してください`
        : ''
    : 'パスワードを入力してください'

  errors.passwordConfirmation = form.passwordConfirmation
    ? form.passwordConfirmation !== form.password
      ? 'パスワードが一致しません'
      : ''
    : '確認用のパスワードを入力してください'

  return !errors.name && !errors.email && !errors.password && !errors.passwordConfirmation
}

function handleSubmit() {
  if (!validate() || !agreed.value) return
  emit('submit', {
    name: form.name.trim(),
    email: form.email.trim(),
    password: form.password,
    role: props.defaultRole === 'company' ? 'company' : 'user',
  })
}
</script>

<template>
  <form class="mt-auth-card" novalidate @submit.prevent="handleSubmit">
    <div class="mt-auth-card__field">
      <label for="signup-name">お名前</label>
      <input
        id="signup-name"
        v-model="form.name"
        type="text"
        autocomplete="name"
        :maxlength="MAX_NAME_LENGTH"
        placeholder="例：山田 太郎"
        :aria-invalid="Boolean(errors.name)"
      />
      <p v-if="errors.name" class="mt-auth-card__error" role="alert">{{ errors.name }}</p>
    </div>

    <div class="mt-auth-card__field">
      <label for="signup-email">メールアドレス</label>
      <input
        id="signup-email"
        v-model="form.email"
        type="email"
        autocomplete="email"
        :placeholder="emailPlaceholder"
        :aria-invalid="Boolean(errors.email)"
      />
      <p v-if="errors.email" class="mt-auth-card__error" role="alert">{{ errors.email }}</p>
    </div>

    <div class="mt-auth-card__field">
      <label for="signup-password">パスワード</label>
      <input
        id="signup-password"
        v-model="form.password"
        type="password"
        autocomplete="new-password"
        placeholder="8文字以上で入力"
        :aria-invalid="Boolean(errors.password)"
      />
      <p v-if="errors.password" class="mt-auth-card__error" role="alert">{{ errors.password }}</p>
    </div>

    <div class="mt-auth-card__field">
      <label for="signup-password-confirmation">パスワード（確認）</label>
      <input
        id="signup-password-confirmation"
        v-model="form.passwordConfirmation"
        type="password"
        autocomplete="new-password"
        placeholder="もう一度入力"
        :aria-invalid="Boolean(errors.passwordConfirmation)"
      />
      <p v-if="errors.passwordConfirmation" class="mt-auth-card__error" role="alert">
        {{ errors.passwordConfirmation }}
      </p>
    </div>

    <label class="mt-auth-card__agreement">
      <input v-model="agreed" type="checkbox" />
      <span>利用規約およびプライバシーポリシーに同意する</span>
    </label>

    <button type="submit" class="mt-auth-card__submit" :disabled="!canSubmit">登録する</button>
  </form>
</template>

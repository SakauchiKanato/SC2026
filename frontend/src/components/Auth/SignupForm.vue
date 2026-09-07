<script setup>
import { reactive } from 'vue'

const MAX_NAME_LENGTH = 100
const MIN_PASSWORD_LENGTH = 8
const MAX_PASSWORD_LENGTH = 72 // bcryptの仕様上72バイトを超える部分は無視されるため(backendと合わせる)
const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

const props = defineProps({
  // 発案者ログイン/企業・自治体ログインどちらの画面から新規登録に来たかに応じて、
  // アカウント種別ラジオボタンの初期値を切り替える（街タネUI）
  defaultRole: {
    type: String,
    default: 'user',
  },
})

const emit = defineEmits(['submit'])

const form = reactive({
  name: '',
  email: '',
  password: '',
  passwordConfirmation: '',
  role: props.defaultRole === 'company' ? 'company' : 'user', // 'user'=発案者, 'company'=地域活性化を検討する企業（usersテーブルのrole列と対応）
})

const errors = reactive({
  name: '',
  email: '',
  password: '',
  passwordConfirmation: '',
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
  if (!validate()) return
  emit('submit', {
    name: form.name.trim(),
    email: form.email.trim(),
    password: form.password,
    role: form.role,
  })
}
</script>

<template>
  <form class="idea-form" novalidate @submit.prevent="handleSubmit">
    <div class="idea-form__field">
      <label for="signup-name">お名前</label>
      <input
        id="signup-name"
        v-model="form.name"
        type="text"
        autocomplete="name"
        :maxlength="MAX_NAME_LENGTH"
        placeholder="例）山田太郎"
        :aria-invalid="Boolean(errors.name)"
      />
      <p v-if="errors.name" class="idea-form__error" role="alert">{{ errors.name }}</p>
    </div>

    <div class="idea-form__field">
      <span class="idea-form__label">アカウント種別</span>
      <div class="idea-form__status-options">
        <label class="idea-form__status-option">
          <input v-model="form.role" type="radio" name="signup-role" value="user" />
          発案者として登録する
        </label>
        <label class="idea-form__status-option">
          <input v-model="form.role" type="radio" name="signup-role" value="company" />
          企業として登録する
        </label>
      </div>
    </div>

    <div class="idea-form__field">
      <label for="signup-email">メールアドレス</label>
      <input
        id="signup-email"
        v-model="form.email"
        type="email"
        autocomplete="email"
        placeholder="例）taro@example.com"
        :aria-invalid="Boolean(errors.email)"
      />
      <p v-if="errors.email" class="idea-form__error" role="alert">{{ errors.email }}</p>
    </div>

    <div class="idea-form__field">
      <label for="signup-password">パスワード</label>
      <input
        id="signup-password"
        v-model="form.password"
        type="password"
        autocomplete="new-password"
        :placeholder="`${MIN_PASSWORD_LENGTH}文字以上`"
        :aria-invalid="Boolean(errors.password)"
      />
      <p v-if="errors.password" class="idea-form__error" role="alert">{{ errors.password }}</p>
    </div>

    <div class="idea-form__field">
      <label for="signup-password-confirmation">パスワード（確認用）</label>
      <input
        id="signup-password-confirmation"
        v-model="form.passwordConfirmation"
        type="password"
        autocomplete="new-password"
        :aria-invalid="Boolean(errors.passwordConfirmation)"
      />
      <p v-if="errors.passwordConfirmation" class="idea-form__error" role="alert">
        {{ errors.passwordConfirmation }}
      </p>
    </div>

    <button type="submit" class="idea-button">登録する</button>
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

.idea-form__field label,
.idea-form__label {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--idea-color-text);
}

.idea-form__status-options {
  display: flex;
  gap: var(--idea-spacing-md);
}

.idea-form__status-option {
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 400;
}

/* :not([type='radio'])で除外しないと、上のアカウント種別ラジオボタンにも
   テキスト入力用の枠線・パディングが付いてしまう */
.idea-form__field input:not([type='radio']) {
  border: 1px solid var(--idea-color-border);
  border-radius: var(--idea-radius);
  padding: 10px 12px;
  font-size: 0.95rem;
  font-family: inherit;
  color: var(--idea-color-text);
  background: var(--idea-color-surface);
}

.idea-form__field input:not([type='radio']):focus {
  outline: 2px solid var(--idea-color-accent);
  outline-offset: 1px;
}

.idea-form__field input:not([type='radio'])[aria-invalid='true'] {
  border-color: var(--idea-color-danger);
}

.idea-form__error {
  margin: 0;
  font-size: 0.8rem;
  color: var(--idea-color-danger);
}
</style>

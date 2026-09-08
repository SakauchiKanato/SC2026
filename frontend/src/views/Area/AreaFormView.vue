<!-- 地域登録フォーム（PDF9枚目）。新規登録専用（編集は地域詳細ページ内で行う） -->
<template>
  <section class="mt-page area-form-view">
    <div class="mt-page__header">
      <h1 class="mt-page__title">地域登録</h1>
      <p class="idea-page-description">あなたの地域についてできる限り詳細に記入してください。</p>
    </div>

    <form @submit.prevent="handleSubmit">
      <div class="mt-form-field">
        <label for="name">地域名</label>
        <input id="name" v-model="form.name" type="text" required maxlength="255" />
        <p v-if="errors.name" class="mt-form-field__error">{{ errors.name }}</p>
      </div>

      <div class="mt-form-field">
        <label for="address">住所（任意）</label>
        <input id="address" v-model="form.address" type="text" maxlength="255" />
        <p v-if="errors.address" class="mt-form-field__error">{{ errors.address }}</p>
      </div>

       <div class="mt-form-field">
        <label for="address">ライフスタイルデータ</label>
        <input id="address" v-model="form.address" type="text" maxlength="255" />
        <p v-if="errors.address" class="mt-form-field__error">{{ errors.address }}</p>
      </div>

      <div class="mt-form-field">
        <label for="challenges">課題点・問題点</label>
        <textarea id="challenges" v-model="form.challenges" rows="4" required></textarea>
        <p v-if="errors.challenges" class="mt-form-field__error">{{ errors.challenges }}</p>
      </div>

      <div class="mt-form-field">
        <label for="expected_future">期待する未来</label>
        <textarea id="expected_future" v-model="form.expectedFuture" rows="4" required></textarea>
        <p v-if="errors.expected_future" class="mt-form-field__error">{{ errors.expected_future }}</p>
      </div>

      <p v-if="submitError" class="idea-banner idea-banner--error">{{ submitError }}</p>

      <div class="area-form-view__actions">
        <button type="submit" class="mt-pill mt-pill--tan" :disabled="isSubmitting">
          {{ isSubmitting ? '登録中...' : '登録完了→' }}
        </button>
        <RouterLink :to="{ name: 'area-list' }">キャンセル</RouterLink>
      </div>
    </form>
  </section>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { createArea } from '@/api/area'

const router = useRouter()

const form = reactive({
  name: '',
  address: '',
  challenges: '',
  expectedFuture: '',
})

const errors = ref({})
const isSubmitting = ref(false)
const submitError = ref('')

async function handleSubmit() {
  isSubmitting.value = true
  submitError.value = ''
  errors.value = {}

  const payload = {
    name: form.name,
    address: form.address === '' ? null : form.address,
    challenges: form.challenges,
    expected_future: form.expectedFuture,
  }

  try {
    const area = await createArea(payload)
    router.push({ name: 'area-detail', params: { id: area.id } })
  } catch (error) {
    if (error.status === 401) {
      submitError.value = 'ログインが必要です'
    } else if (error.status === 422 && error.errors) {
      errors.value = error.errors
    } else {
      submitError.value = '登録に失敗しました'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.area-form-view__actions {
  display: flex;
  gap: 16px;
  align-items: center;
  margin-top: var(--idea-spacing-md);
}
</style>

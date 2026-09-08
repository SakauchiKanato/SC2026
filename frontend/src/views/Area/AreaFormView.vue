<!-- 地域登録フォーム（PDF9枚目「地域登録フォーム」に準拠）。新規登録専用（編集は地域詳細ページ内で行う） -->
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

      <div class="area-form-view__section">
        <h2 class="area-form-view__section-title">ライフスタイルデータ（任意）</h2>
        <p class="idea-page-description">
          分かる範囲で構いません。地域の特色として、他のユーザーに表示されます。
        </p>

        <div class="mt-form-field">
          <label for="address">住所</label>
          <input
            id="address"
            v-model="form.address"
            type="text"
            maxlength="255"
            placeholder="例：東京都渋谷区宇田川町1-1"
          />
          <p v-if="errors.address" class="mt-form-field__error">{{ errors.address }}</p>
        </div>

        <div class="mt-form-row">
          <div class="mt-form-field">
            <label for="population">人口</label>
            <input
              id="population"
              v-model="form.population"
              type="text"
              maxlength="255"
              placeholder="例：約22.6万人"
            />
            <p v-if="errors.population" class="mt-form-field__error">{{ errors.population }}</p>
          </div>

          <div class="mt-form-field">
            <label for="day_night_population_ratio">昼夜人口比率</label>
            <input
              id="day_night_population_ratio"
              v-model="form.dayNightPopulationRatio"
              type="text"
              maxlength="255"
              placeholder="例：約230%"
            />
            <p v-if="errors.day_night_population_ratio" class="mt-form-field__error">
              {{ errors.day_night_population_ratio }}
            </p>
          </div>
        </div>

        <div class="mt-form-row">
          <div class="mt-form-field">
            <label for="average_age">平均年齢</label>
            <input
              id="average_age"
              v-model="form.averageAge"
              type="text"
              maxlength="255"
              placeholder="例：38.4歳"
            />
            <p v-if="errors.average_age" class="mt-form-field__error">{{ errors.average_age }}</p>
          </div>

          <div class="mt-form-field">
            <label for="main_industry">主要産業</label>
            <input
              id="main_industry"
              v-model="form.mainIndustry"
              type="text"
              maxlength="255"
              placeholder="例：商業・サービス業 / IT"
            />
            <p v-if="errors.main_industry" class="mt-form-field__error">{{ errors.main_industry }}</p>
          </div>
        </div>

        <div class="mt-form-field">
          <label for="transit_access">交通アクセス</label>
          <input
            id="transit_access"
            v-model="form.transitAccess"
            type="text"
            maxlength="255"
            placeholder="例：JR山手線・私鉄5路線が乗り入れる広域ターミナル"
          />
          <p v-if="errors.transit_access" class="mt-form-field__error">{{ errors.transit_access }}</p>
        </div>
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
  population: '',
  dayNightPopulationRatio: '',
  averageAge: '',
  mainIndustry: '',
  transitAccess: '',
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
    population: form.population === '' ? null : form.population,
    day_night_population_ratio:
      form.dayNightPopulationRatio === '' ? null : form.dayNightPopulationRatio,
    average_age: form.averageAge === '' ? null : form.averageAge,
    main_industry: form.mainIndustry === '' ? null : form.mainIndustry,
    transit_access: form.transitAccess === '' ? null : form.transitAccess,
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
.area-form-view__section {
  margin: var(--idea-spacing-lg) 0;
  padding-top: var(--idea-spacing-md);
  border-top: 1px solid var(--mt-color-border);
}

.area-form-view__section-title {
  font-size: 1.05rem;
  margin: 0 0 4px;
}

.area-form-view__actions {
  display: flex;
  gap: 16px;
  align-items: center;
  margin-top: var(--idea-spacing-md);
}
</style>

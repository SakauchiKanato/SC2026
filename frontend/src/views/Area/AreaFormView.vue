<!--
  地域登録フォーム（machitane-design/RegisterForm.dc.html準拠）。新規登録専用
  （編集は地域詳細ページ内で行う）。

  登録完了後は、デザインに完了画面はあるがボタンが無い（LoginView.vueのログイン
  成功時と同じパターン）ため、一定時間表示してから登録した地域の詳細ページへ
  自動的に遷移する。
-->
<template>
  <section class="mt-page area-form-view">
    <template v-if="!submitted">
      <RouterLink :to="{ name: 'area-list' }" class="mt-back-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
          <path d="M19 12H5M11 18l-6-6 6-6" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        地域一覧に戻る
      </RouterLink>

      <div class="mt-page__header">
        <h1 class="mt-page__title">地域登録</h1>
        <p class="idea-page-description">
          あなたの地域について、できる限り詳細に記入してください。集まった情報は、似た地域で挑戦する人の参考になります。
        </p>
      </div>

      <form class="area-form-view__card" @submit.prevent="handleSubmit">
        <div class="area-form-view__field">
          <label for="name">地域名 <span class="area-form-view__required">必須</span></label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            maxlength="255"
            placeholder="例：東京都渋谷区"
          />
          <p v-if="errors.name" class="area-form-view__error">{{ errors.name }}</p>
        </div>

        <div class="area-form-view__divider"></div>

        <h2 class="area-form-view__section-title">ライフスタイルデータ（任意）</h2>
        <p class="area-form-view__section-note">
          分かる範囲で構いません。地域の特色として、他のユーザーに表示されます。
        </p>

        <div class="area-form-view__grid">
          <div class="area-form-view__field area-form-view__field--wide">
            <label for="address">住所</label>
            <input
              id="address"
              v-model="form.address"
              type="text"
              maxlength="255"
              placeholder="例：東京都渋谷区宇田川町1-1"
            />
            <p v-if="errors.address" class="area-form-view__error">{{ errors.address }}</p>
          </div>

          <div class="area-form-view__field">
            <label for="population">人口</label>
            <input
              id="population"
              v-model="form.population"
              type="text"
              maxlength="255"
              placeholder="例：約22.6万人"
            />
            <p v-if="errors.population" class="area-form-view__error">{{ errors.population }}</p>
          </div>

          <div class="area-form-view__field">
            <label for="day_night_population_ratio">昼夜人口比率</label>
            <input
              id="day_night_population_ratio"
              v-model="form.dayNightPopulationRatio"
              type="text"
              maxlength="255"
              placeholder="例：約230%"
            />
            <p v-if="errors.day_night_population_ratio" class="area-form-view__error">
              {{ errors.day_night_population_ratio }}
            </p>
          </div>

          <div class="area-form-view__field">
            <label for="average_age">平均年齢</label>
            <input
              id="average_age"
              v-model="form.averageAge"
              type="text"
              maxlength="255"
              placeholder="例：38.4歳"
            />
            <p v-if="errors.average_age" class="area-form-view__error">{{ errors.average_age }}</p>
          </div>

          <div class="area-form-view__field">
            <label for="main_industry">主要産業</label>
            <input
              id="main_industry"
              v-model="form.mainIndustry"
              type="text"
              maxlength="255"
              placeholder="例：商業・サービス業 / IT"
            />
            <p v-if="errors.main_industry" class="area-form-view__error">{{ errors.main_industry }}</p>
          </div>

          <div class="area-form-view__field area-form-view__field--wide">
            <label for="transit_access">交通アクセス</label>
            <input
              id="transit_access"
              v-model="form.transitAccess"
              type="text"
              maxlength="255"
              placeholder="例：JR山手線・私鉄5路線が乗り入れる広域ターミナル"
            />
            <p v-if="errors.transit_access" class="area-form-view__error">{{ errors.transit_access }}</p>
          </div>
        </div>

        <div class="area-form-view__divider"></div>

        <div class="area-form-view__field">
          <div class="area-form-view__field-head">
            <label for="challenges">課題点・問題点 <span class="area-form-view__required">必須</span></label>
            <span class="area-form-view__char-count">{{ form.challenges.length }} 文字</span>
          </div>
          <textarea
            id="challenges"
            v-model="form.challenges"
            rows="4"
            placeholder="例：駅前の商店街に空き店舗が増え、日中のにぎわいが失われつつある"
          ></textarea>
          <p v-if="errors.challenges" class="area-form-view__error">{{ errors.challenges }}</p>
        </div>

        <div class="area-form-view__field">
          <div class="area-form-view__field-head">
            <label for="expected_future">期待する未来 <span class="area-form-view__required">必須</span></label>
            <span class="area-form-view__char-count">{{ form.expectedFuture.length }} 文字</span>
          </div>
          <textarea
            id="expected_future"
            v-model="form.expectedFuture"
            rows="4"
            placeholder="例：世代を問わず人が集まり、地域の店にまた活気が戻っている状態"
          ></textarea>
          <p v-if="errors.expected_future" class="area-form-view__error">{{ errors.expected_future }}</p>
        </div>

        <p v-if="submitError" class="idea-banner idea-banner--error">{{ submitError }}</p>

        <div class="area-form-view__footer">
          <span class="area-form-view__footer-hint">必須項目を入力すると登録できます</span>
          <button type="submit" class="area-detail-view__cta-btn" :disabled="!canSubmit || isSubmitting">
            {{ isSubmitting ? '登録中...' : '登録完了' }}
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
              <path d="M5 12h14M13 6l6 6-6 6" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>
        </div>
      </form>
    </template>

    <div v-else class="area-form-view__success">
      <div class="area-form-view__success-icon">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none">
          <path d="M5 13l4 4L19 7" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>
      <h1 class="area-form-view__success-title">地域登録が完了しました</h1>
      <p class="area-form-view__success-text">
        「{{ form.name }}」の情報を登録しました。企業・自治体からのリクエストが届くとお知らせします。
      </p>
    </div>
  </section>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { createArea } from '@/api/area'

const REGISTER_SUCCESS_DISPLAY_MS = 1200

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
const submitted = ref(false)

const canSubmit = computed(
  () => form.name.trim() && form.challenges.trim() && form.expectedFuture.trim(),
)

async function handleSubmit() {
  if (!canSubmit.value) return

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
    submitted.value = true
    setTimeout(() => {
      router.push({ name: 'area-detail', params: { id: area.id } })
    }, REGISTER_SUCCESS_DISPLAY_MS)
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
.area-form-view__card {
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: 22px;
  padding: 36px;
  box-shadow: var(--shadow-sm);
}

.area-form-view__field {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: var(--idea-spacing-lg);
}

.area-form-view__field-head {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
}

.area-form-view__field label {
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--ink);
}

.area-form-view__required {
  color: var(--brand-700);
}

.area-form-view__char-count {
  font-size: 0.75rem;
  color: var(--ink-faint);
}

.area-form-view__field input,
.area-form-view__field textarea {
  width: 100%;
  padding: 13px 16px;
  border: 1px solid var(--line);
  border-radius: 12px;
  font-size: 0.9rem;
  font-family: inherit;
  color: var(--ink);
  background: var(--bg);
  resize: vertical;
}

.area-form-view__field input:focus,
.area-form-view__field textarea:focus {
  outline: none;
  border-color: var(--brand-500);
  box-shadow: 0 0 0 3px oklch(56% 0.1 146 / 0.14);
}

.area-form-view__error {
  margin: 0;
  font-size: 0.8rem;
  color: var(--error);
}

.area-form-view__divider {
  height: 1px;
  background: var(--line);
  margin-bottom: var(--idea-spacing-lg);
}

.area-form-view__section-title {
  font-size: 0.85rem;
  font-weight: 700;
  margin: 0 0 6px;
}

.area-form-view__section-note {
  font-size: 0.75rem;
  color: var(--ink-faint);
  line-height: 1.7;
  margin: 0 0 var(--idea-spacing-md);
}

.area-form-view__grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--idea-spacing-md);
  margin-bottom: var(--idea-spacing-lg);
}

.area-form-view__grid .area-form-view__field {
  margin-bottom: 0;
}

.area-form-view__field--wide {
  grid-column: span 2;
}

@media (max-width: 640px) {
  .area-form-view__grid {
    grid-template-columns: 1fr;
  }

  .area-form-view__field--wide {
    grid-column: span 1;
  }
}

.area-form-view__footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 14px;
  flex-wrap: wrap;
}

.area-form-view__footer-hint {
  font-size: 0.78rem;
  color: var(--ink-faint);
  margin-right: auto;
}

.area-form-view__success {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 120px 0;
}

.area-form-view__success-icon {
  width: 64px;
  height: 64px;
  border-radius: var(--mt-radius-pill);
  background: var(--surface-sage);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 22px;
  stroke: var(--brand-900);
}

.area-form-view__success-title {
  font-family: 'Zen Maru Gothic', 'Noto Sans JP', sans-serif;
  font-size: 1.3rem;
  font-weight: 700;
  margin: 0 0 10px;
}

.area-form-view__success-text {
  font-size: 0.9rem;
  color: var(--ink-soft);
  max-width: 420px;
  line-height: 1.8;
  margin: 0;
}
</style>

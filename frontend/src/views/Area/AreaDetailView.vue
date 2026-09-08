<!--
  地域の特色詳細ページ。
  発案者（＝その地域の登録者ではないユーザー）が見る場合は閲覧のみ＋
  machitane-design/RegionDetail.dc.html準拠のインライン展開式「アイデア登録」導線。
  地域の登録者本人（企業・自治体側であることが多い想定）が見る場合は、
  「ライフスタイルデータ」「課題点・問題点」「期待する未来」を編集できる
  （このオーナー向け編集フォームのデザイン刷新は別対応で行う）。

  NOTE: RegionDetail.dc.htmlの「企業・自治体が実現したいこと」は複数団体分の要望リスト
        (sc-for)だが、実際のデータはareaにつき登録者(challenges/expected_future)は
        1組のみで、登録者の団体名もAPIから取得できない（user_idのみ）。そのため
        件数表示・団体名バッジは無しの単一カードとして表示している。
-->
<template>
  <section class="mt-page area-detail-view">
    <p v-if="isLoading" class="idea-loading-indicator">読み込み中です…</p>
    <p v-else-if="loadError" class="idea-banner idea-banner--error">{{ loadError }}</p>

    <template v-else-if="area">
      <RouterLink :to="backLinkRoute" class="area-detail-view__back">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
          <path d="M19 12H5M11 18l-6-6 6-6" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        地域一覧に戻る
      </RouterLink>

      <h1 class="mt-page__title">{{ area.name }}の特色</h1>

      <p v-if="isOwner" class="mt-section-label">ライフスタイルデータ</p>

      <form v-if="isOwner" @submit.prevent="handleSave">
        <div class="mt-form-field">
          <label for="edit-address">住所</label>
          <input
            id="edit-address"
            v-model="editForm.address"
            type="text"
            maxlength="255"
            placeholder="例：東京都渋谷区宇田川町1-1"
          />
        </div>

        <div class="mt-form-row">
          <div class="mt-form-field">
            <label for="edit-population">人口</label>
            <input
              id="edit-population"
              v-model="editForm.population"
              type="text"
              maxlength="255"
              placeholder="例：約22.6万人"
            />
          </div>
          <div class="mt-form-field">
            <label for="edit-day-night-population-ratio">昼夜人口比率</label>
            <input
              id="edit-day-night-population-ratio"
              v-model="editForm.dayNightPopulationRatio"
              type="text"
              maxlength="255"
              placeholder="例：約230%"
            />
          </div>
        </div>

        <div class="mt-form-row">
          <div class="mt-form-field">
            <label for="edit-average-age">平均年齢</label>
            <input
              id="edit-average-age"
              v-model="editForm.averageAge"
              type="text"
              maxlength="255"
              placeholder="例：38.4歳"
            />
          </div>
          <div class="mt-form-field">
            <label for="edit-main-industry">主要産業</label>
            <input
              id="edit-main-industry"
              v-model="editForm.mainIndustry"
              type="text"
              maxlength="255"
              placeholder="例：商業・サービス業 / IT"
            />
          </div>
        </div>

        <div class="mt-form-field">
          <label for="edit-transit-access">交通アクセス</label>
          <input
            id="edit-transit-access"
            v-model="editForm.transitAccess"
            type="text"
            maxlength="255"
            placeholder="例：JR山手線・私鉄5路線が乗り入れる広域ターミナル"
          />
        </div>

        <p class="mt-section-label">企業・自治体が実現したいこと</p>
        <div class="mt-box-row">
          <div class="mt-box">
            <textarea v-model="editForm.challenges" placeholder="課題点・問題点"></textarea>
          </div>
          <div class="mt-box">
            <textarea v-model="editForm.expectedFuture" placeholder="期待する未来"></textarea>
          </div>
        </div>

        <p v-if="saveError" class="idea-banner idea-banner--error">{{ saveError }}</p>

        <div class="area-detail-view__actions">
          <button type="submit" class="mt-pill mt-pill--tan" :disabled="isSaving">
            {{ isSaving ? '保存中...' : '変更→' }}
          </button>
        </div>
      </form>

      <template v-else>
        <div class="area-detail-view__grid">
          <section class="area-detail-view__panel">
            <div class="area-detail-view__panel-head">
              <h2 class="area-detail-view__panel-title">ライフスタイルデータ</h2>
              <span class="area-detail-view__panel-note">参考データ（イメージ）</span>
            </div>
            <div class="area-detail-view__stat-grid">
              <div class="area-detail-view__stat">
                <p class="area-detail-view__stat-label">人口</p>
                <p class="area-detail-view__stat-value">{{ area.population || '（未記入）' }}</p>
              </div>
              <div class="area-detail-view__stat">
                <p class="area-detail-view__stat-label">昼夜人口比率</p>
                <p class="area-detail-view__stat-value">{{ area.day_night_population_ratio || '（未記入）' }}</p>
              </div>
              <div class="area-detail-view__stat">
                <p class="area-detail-view__stat-label">平均年齢</p>
                <p class="area-detail-view__stat-value">{{ area.average_age || '（未記入）' }}</p>
              </div>
              <div class="area-detail-view__stat">
                <p class="area-detail-view__stat-label">主要産業</p>
                <p class="area-detail-view__stat-value">{{ area.main_industry || '（未記入）' }}</p>
              </div>
              <div class="area-detail-view__stat">
                <p class="area-detail-view__stat-label">住所</p>
                <p class="area-detail-view__stat-value">{{ area.address || '（未記入）' }}</p>
              </div>
              <div class="area-detail-view__stat area-detail-view__stat--wide">
                <p class="area-detail-view__stat-label">交通アクセス</p>
                <p class="area-detail-view__stat-value">{{ area.transit_access || '（未記入）' }}</p>
              </div>
            </div>
          </section>

          <section class="area-detail-view__panel">
            <h2 class="area-detail-view__panel-title">企業・自治体が<br />実現したいこと</h2>
            <div class="area-detail-view__request-card">
              <div>
                <p class="area-detail-view__request-label">課題点・問題点</p>
                <p class="area-detail-view__request-text">{{ area.challenges || '（未記入）' }}</p>
              </div>
              <div>
                <p class="area-detail-view__request-label">期待する未来</p>
                <p class="area-detail-view__request-text">{{ area.expected_future || '（未記入）' }}</p>
              </div>
            </div>
          </section>
        </div>

        <!-- アイデア登録は発案者（role=user）専用。企業・自治体が他社の地域を見ている場合は表示しない -->
        <div v-if="user?.role === 'user'" class="area-detail-view__idea-panel">
          <div v-if="ideaSubmitted" class="area-detail-view__idea-success">
            <div class="area-detail-view__idea-success-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M5 13l4 4L19 7" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
            <p class="area-detail-view__idea-success-title">アイデアを登録しました</p>
            <p class="area-detail-view__idea-success-text">
              ご協力ありがとうございます。企業・自治体からの反応があり次第お知らせします。
            </p>
          </div>

          <template v-else>
            <div v-if="!ideaFormOpen" class="area-detail-view__idea-prompt">
              <div>
                <p class="area-detail-view__idea-prompt-title">この地域でアイデアを登録する</p>
                <p class="area-detail-view__idea-prompt-text">あなたの発案を、地域の企業・自治体に届けましょう。</p>
              </div>
              <button type="button" class="area-detail-view__cta-btn" @click="ideaFormOpen = true">
                アイデア登録はこちらから
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                  <path d="M5 12h14M13 6l6 6-6 6" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </button>
            </div>

            <div v-else>
              <p class="area-detail-view__idea-prompt-title">あなたのアイデアを登録する</p>
              <p v-if="ideaSubmitError" class="idea-banner idea-banner--error">{{ ideaSubmitError }}</p>
              <IdeaForm @submit="handleIdeaSubmit" />
              <button
                type="button"
                class="area-detail-view__idea-cancel"
                :disabled="isSubmittingIdea"
                @click="ideaFormOpen = false"
              >
                キャンセル
              </button>
            </div>
          </template>
        </div>
      </template>
    </template>
  </section>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { fetchArea, updateArea } from '@/api/area'
import { useAuthStore } from '../../store/auth'
import { useIdeaStore } from '../../store/idea'
import IdeaForm from '../../components/Idea/IdeaForm.vue'

const props = defineProps({
  id: {
    type: [String, Number],
    required: true,
  },
})

const { user, isAuthenticated } = useAuthStore()
const { registerIdea } = useIdeaStore()

const area = ref(null)
const isLoading = ref(true)
const loadError = ref('')

const isSaving = ref(false)
const saveError = ref('')

// 発案者はarea-list（企業・自治体専用）を持たないため「地域一覧」代わりにhomeへ、
// 企業・自治体（自分の地域ではない他社の地域を見ている場合）はarea-listへ戻す
const backLinkRoute = computed(() =>
  user.value?.role === 'company' ? { name: 'area-list' } : { name: 'home' },
)

const editForm = reactive({
  address: '',
  population: '',
  dayNightPopulationRatio: '',
  averageAge: '',
  mainIndustry: '',
  transitAccess: '',
  challenges: '',
  expectedFuture: '',
})

// PostgreSQL(PDO)からのuser_idは文字列で返るため、数値化して比較する
const isOwner = computed(
  () =>
    isAuthenticated.value &&
    area.value &&
    Number(user.value?.id) === Number(area.value.user_id)
)

// インライン展開式のアイデア登録フォーム（RegionDetail.dc.html準拠）
const ideaFormOpen = ref(false)
const ideaSubmitted = ref(false)
const ideaSubmitError = ref('')
const isSubmittingIdea = ref(false)

async function handleIdeaSubmit(payload) {
  isSubmittingIdea.value = true
  ideaSubmitError.value = ''
  try {
    await registerIdea(area.value.id, payload)
    ideaFormOpen.value = false
    ideaSubmitted.value = true
  } catch (error) {
    ideaSubmitError.value = error.message ?? 'アイデアの登録に失敗しました'
  } finally {
    isSubmittingIdea.value = false
  }
}

async function loadArea() {
  isLoading.value = true
  loadError.value = ''
  try {
    area.value = await fetchArea(props.id)
    editForm.address = area.value.address ?? ''
    editForm.population = area.value.population ?? ''
    editForm.dayNightPopulationRatio = area.value.day_night_population_ratio ?? ''
    editForm.averageAge = area.value.average_age ?? ''
    editForm.mainIndustry = area.value.main_industry ?? ''
    editForm.transitAccess = area.value.transit_access ?? ''
    editForm.challenges = area.value.challenges ?? ''
    editForm.expectedFuture = area.value.expected_future ?? ''
  } catch (error) {
    loadError.value = '地域データの取得に失敗しました'
  } finally {
    isLoading.value = false
  }
}

async function handleSave() {
  isSaving.value = true
  saveError.value = ''

  const payload = {
    name: area.value.name,
    address: editForm.address === '' ? null : editForm.address,
    population: editForm.population === '' ? null : editForm.population,
    day_night_population_ratio:
      editForm.dayNightPopulationRatio === '' ? null : editForm.dayNightPopulationRatio,
    average_age: editForm.averageAge === '' ? null : editForm.averageAge,
    main_industry: editForm.mainIndustry === '' ? null : editForm.mainIndustry,
    transit_access: editForm.transitAccess === '' ? null : editForm.transitAccess,
    challenges: editForm.challenges,
    expected_future: editForm.expectedFuture,
    tags: (area.value.tags || []).map((tag) => tag.name),
  }

  try {
    area.value = await updateArea(props.id, payload)
  } catch (error) {
    if (error.status === 403) {
      saveError.value = 'この地域を編集する権限がありません'
    } else if (error.status === 422 && error.errors) {
      saveError.value = Object.values(error.errors).join(' / ')
    } else {
      saveError.value = '保存に失敗しました'
    }
  } finally {
    isSaving.value = false
  }
}

onMounted(loadArea)
</script>

<style scoped>
.area-detail-view__actions {
  margin-top: var(--idea-spacing-md);
  display: flex;
  justify-content: flex-end;
}

.area-detail-view__back {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--ink-soft);
  stroke: var(--ink-soft);
  margin-bottom: var(--idea-spacing-md);
}

.area-detail-view__back:hover {
  color: var(--brand-900);
}

.area-detail-view__grid {
  display: grid;
  grid-template-columns: 1.6fr 1fr;
  gap: var(--idea-spacing-md);
  align-items: start;
  margin-top: var(--idea-spacing-md);
}

@media (max-width: 900px) {
  .area-detail-view__grid {
    grid-template-columns: 1fr;
  }
}

.area-detail-view__panel {
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: 22px;
  padding: 28px;
  box-shadow: var(--shadow-sm);
}

.area-detail-view__panel-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.area-detail-view__panel-title {
  font-family: 'Zen Maru Gothic', 'Noto Sans JP', sans-serif;
  font-size: 1.05rem;
  font-weight: 700;
  margin: 0 0 var(--idea-spacing-md);
}

.area-detail-view__panel-note {
  font-size: 0.75rem;
  color: var(--ink-faint);
  white-space: nowrap;
}

.area-detail-view__stat-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--idea-spacing-md);
}

.area-detail-view__stat {
  padding: 16px 18px;
  background: var(--bg);
  border-radius: 14px;
}

.area-detail-view__stat--wide {
  grid-column: span 2;
}

.area-detail-view__stat-label {
  font-size: 0.78rem;
  color: var(--ink-faint);
  margin: 0 0 6px;
}

.area-detail-view__stat-value {
  font-family: 'Zen Maru Gothic', 'Noto Sans JP', sans-serif;
  font-size: 1.15rem;
  font-weight: 700;
  margin: 0;
  line-height: 1.5;
}

.area-detail-view__request-card {
  display: flex;
  flex-direction: column;
  gap: var(--idea-spacing-md);
}

.area-detail-view__request-label {
  font-size: 0.72rem;
  font-weight: 700;
  color: var(--ink-faint);
  margin: 0 0 4px;
}

.area-detail-view__request-text {
  font-size: 0.85rem;
  line-height: 1.7;
  color: var(--ink);
  margin: 0;
  white-space: pre-wrap;
}

/* インライン展開式のアイデア登録パネル */
.area-detail-view__idea-panel {
  margin-top: var(--idea-spacing-lg);
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: 22px;
  padding: 28px;
  box-shadow: var(--shadow-sm);
}

.area-detail-view__idea-prompt {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--idea-spacing-md);
  flex-wrap: wrap;
}

.area-detail-view__idea-prompt-title {
  font-family: 'Zen Maru Gothic', 'Noto Sans JP', sans-serif;
  font-size: 1.05rem;
  font-weight: 700;
  margin: 0 0 6px;
}

.area-detail-view__idea-prompt-text {
  font-size: 0.85rem;
  color: var(--ink-soft);
  margin: 0;
}

.area-detail-view__cta-btn {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 14px 26px;
  border: none;
  border-radius: var(--mt-radius-pill);
  background: var(--brand-700);
  color: #ffffff;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.area-detail-view__cta-btn:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.area-detail-view__idea-cancel {
  margin-top: 8px;
  padding: 10px 4px;
  border: none;
  background: transparent;
  color: var(--ink-soft);
  font-size: 0.85rem;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
}

.area-detail-view__idea-cancel:hover {
  color: var(--brand-900);
}

.area-detail-view__idea-success {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 12px 0;
}

.area-detail-view__idea-success-icon {
  width: 52px;
  height: 52px;
  border-radius: var(--mt-radius-pill);
  background: var(--surface-sage);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
  stroke: var(--brand-900);
}

.area-detail-view__idea-success-title {
  font-family: 'Zen Maru Gothic', 'Noto Sans JP', sans-serif;
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0 0 8px;
}

.area-detail-view__idea-success-text {
  font-size: 0.85rem;
  color: var(--ink-soft);
  margin: 0;
}
</style>

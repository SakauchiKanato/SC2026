<!--
  地域の特色詳細ページ。
  発案者（＝その地域の登録者ではないユーザー）が見る場合は閲覧のみ＋
  machitane-design/RegionDetail.dc.html準拠のインライン展開式「アイデア登録」導線。
  地域の登録者本人（企業・自治体側であることが多い想定）が見る場合は、
  RegionDetailCompanyView.dc.html（確認画面）→RegionDetailEdit.dc.html（編集画面）の
  2段階。デフォルトは確認画面（読み取り専用）で、「編集する→」を押すと編集画面
  （ownerEditMode）に切り替わる。両画面ともルートは分けず、同じページ内の表示切替。

  NOTE: RegionDetail*.dc.htmlの「企業・自治体が実現したいこと」は複数団体分の要望
        リスト（sc-for、追加・削除・「他の団体のリクエスト」表示）だが、実際のデータは
        areaにつき登録者(challenges/expected_future)は1組のみで、登録者の団体名も
        APIから取得できない（user_idのみ）。そのため件数表示・追加/削除・他団体表示は
        無く、自分の団体（ログイン中のuser.name）のリクエストを1件だけ編集する形にして
        いる。複数団体対応は、その機能を開発中のチームメイトの作業がマージされてから
        別途対応する。

  NOTE: RegionDetailEdit.dc.htmlの「その他」自由記述欄は、対応するカラムが
        バックエンドに無いため今回は追加していない（同じくチームメイトの対応待ち）。
-->
<template>
  <section class="mt-page area-detail-view">
    <p v-if="isLoading" class="idea-loading-indicator">読み込み中です…</p>
    <p v-else-if="loadError" class="idea-banner idea-banner--error">{{ loadError }}</p>

    <template v-else-if="area">
      <RouterLink :to="backLinkRoute" class="mt-back-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
          <path d="M19 12H5M11 18l-6-6 6-6" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        地域一覧に戻る
      </RouterLink>

      <div class="area-detail-view__title-row">
        <h1 class="mt-page__title">
          {{ area.name }}の特色<template v-if="isOwner && ownerEditMode">を編集</template>
        </h1>
        <span v-if="isOwner && ownerEditMode" class="area-detail-view__edit-badge">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
            <path
              d="M11 4H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-4M18.5 2.5a2.1 2.1 0 013 3L12 15l-4 1 1-4 9.5-9.5z"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
          編集モード
        </span>
      </div>

      <template v-if="isOwner">
        <!-- 確認画面（読み取り専用）。RegionDetailCompanyView.dc.html準拠 -->
        <template v-if="!ownerEditMode">
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
              <div class="area-detail-view__request-card area-detail-view__request-card--mine">
                <span class="area-detail-view__request-badge">{{ user?.name }}（あなたの団体）</span>
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

          <div class="area-detail-view__idea-panel">
            <div class="area-detail-view__idea-prompt">
              <div>
                <p class="area-detail-view__idea-prompt-title">この地域の情報を編集しますか？</p>
                <p class="area-detail-view__idea-prompt-text">
                  ライフスタイルデータや、あなたの団体のリクエスト内容を変更できます。
                </p>
              </div>
              <button type="button" class="area-detail-view__cta-btn" @click="ownerEditMode = true">
                編集する
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                  <path
                    d="M11 4H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-4M18.5 2.5a2.1 2.1 0 013 3L12 15l-4 1 1-4 9.5-9.5z"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
              </button>
            </div>
          </div>
        </template>

        <!-- 編集画面。RegionDetailEdit.dc.html準拠 -->
        <form v-else @submit.prevent="handleSave">
          <div class="area-detail-view__grid">
            <section class="area-detail-view__panel">
              <div class="area-detail-view__panel-head">
                <h2 class="area-detail-view__panel-title">ライフスタイルデータ</h2>
                <span class="area-detail-view__panel-note">編集可能</span>
              </div>
              <div class="area-detail-view__edit-grid">
                <div class="area-detail-view__edit-field">
                  <label for="edit-population">人口</label>
                  <input
                    id="edit-population"
                    v-model="editForm.population"
                    type="text"
                    maxlength="255"
                    placeholder="例：約22.6万人"
                  />
                </div>
                <div class="area-detail-view__edit-field">
                  <label for="edit-day-night-population-ratio">昼夜人口比率</label>
                  <input
                    id="edit-day-night-population-ratio"
                    v-model="editForm.dayNightPopulationRatio"
                    type="text"
                    maxlength="255"
                    placeholder="例：約230%"
                  />
                </div>
                <div class="area-detail-view__edit-field">
                  <label for="edit-average-age">平均年齢</label>
                  <input
                    id="edit-average-age"
                    v-model="editForm.averageAge"
                    type="text"
                    maxlength="255"
                    placeholder="例：38.4歳"
                  />
                </div>
                <div class="area-detail-view__edit-field">
                  <label for="edit-main-industry">主要産業</label>
                  <input
                    id="edit-main-industry"
                    v-model="editForm.mainIndustry"
                    type="text"
                    maxlength="255"
                    placeholder="例：商業・サービス業 / IT"
                  />
                </div>
                <div class="area-detail-view__edit-field area-detail-view__edit-field--wide">
                  <label for="edit-address">住所</label>
                  <input
                    id="edit-address"
                    v-model="editForm.address"
                    type="text"
                    maxlength="255"
                    placeholder="例：東京都渋谷区宇田川町1-1"
                  />
                </div>
                <div class="area-detail-view__edit-field area-detail-view__edit-field--wide">
                  <label for="edit-transit-access">交通アクセス</label>
                  <input
                    id="edit-transit-access"
                    v-model="editForm.transitAccess"
                    type="text"
                    maxlength="255"
                    placeholder="例：JR山手線・私鉄5路線が乗り入れる広域ターミナル"
                  />
                </div>
              </div>
            </section>

            <section class="area-detail-view__panel">
              <h2 class="area-detail-view__panel-title">企業・自治体が<br />実現したいこと</h2>
              <div class="area-detail-view__request-card area-detail-view__request-card--mine">
                <span class="area-detail-view__request-badge">{{ user?.name }}（あなたの団体）</span>
                <div class="area-detail-view__edit-field">
                  <label for="edit-challenges">課題点・問題点</label>
                  <textarea id="edit-challenges" v-model="editForm.challenges" rows="3"></textarea>
                </div>
                <div class="area-detail-view__edit-field">
                  <label for="edit-expected-future">期待する未来</label>
                  <textarea id="edit-expected-future" v-model="editForm.expectedFuture" rows="3"></textarea>
                </div>
              </div>
            </section>
          </div>

          <div class="area-detail-view__idea-panel">
            <p v-if="saveError" class="idea-banner idea-banner--error">{{ saveError }}</p>

            <div v-if="saveSucceeded" class="area-detail-view__idea-success">
              <div class="area-detail-view__idea-success-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                  <path d="M5 13l4 4L19 7" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </div>
              <p class="area-detail-view__idea-success-title">変更を保存しました</p>
              <p class="area-detail-view__idea-success-text">
                地域の情報と、あなたの団体のリクエストを更新しました。
              </p>
            </div>

            <div v-else class="area-detail-view__idea-prompt">
              <div>
                <p class="area-detail-view__idea-prompt-title">変更を保存する</p>
                <p class="area-detail-view__idea-prompt-text">
                  ライフスタイルデータとあなたの団体のリクエストを更新します。
                </p>
              </div>
              <button type="submit" class="area-detail-view__cta-btn" :disabled="isSaving">
                {{ isSaving ? '保存中...' : '変更を保存する' }}
              </button>
            </div>
          </div>
        </form>
      </template>

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
import { reactive, ref, computed, watch, onMounted } from 'vue'
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
const saveSucceeded = ref(false)

// オーナー向けの確認画面（デフォルト）⇔編集画面の切り替え（RegionDetailCompanyView.dc.html
// ⇔RegionDetailEdit.dc.html）。ルートは分けず、同じページ内の表示切替にしている
const ownerEditMode = ref(false)

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

// 編集内容を変更したら「保存しました」表示を消す（RegionDetailEdit.dc.html準拠）
watch(editForm, () => {
  saveSucceeded.value = false
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
    saveSucceeded.value = true
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
.area-detail-view__title-row {
  display: flex;
  align-items: center;
  gap: var(--idea-spacing-md);
  flex-wrap: wrap;
  margin-bottom: 6px;
}

.area-detail-view__title-row .mt-page__title {
  margin: 0;
}

.area-detail-view__edit-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 16px;
  border-radius: var(--mt-radius-pill);
  background: var(--surface-sage-soft);
  color: var(--brand-900);
  stroke: var(--brand-900);
  font-size: 0.78rem;
  font-weight: 700;
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

/* オーナー（自分の団体）の要望カード。他団体との区別が今は無いため常にハイライト表示 */
.area-detail-view__request-card--mine {
  padding: 18px;
  background: var(--surface-sage-soft);
  border-radius: 16px;
}

.area-detail-view__request-badge {
  display: inline-flex;
  font-size: 0.72rem;
  font-weight: 700;
  color: var(--brand-900);
  background: oklch(99% 0.005 90 / 0.6);
  padding: 4px 12px;
  border-radius: var(--mt-radius-pill);
  margin-bottom: var(--idea-spacing-md);
}

/* オーナー編集フォームの入力欄グリッド */
.area-detail-view__edit-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--idea-spacing-md);
}

.area-detail-view__edit-field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.area-detail-view__edit-field:not(:first-child) {
  margin-top: var(--idea-spacing-md);
}

.area-detail-view__edit-grid .area-detail-view__edit-field {
  margin-top: 0;
}

.area-detail-view__edit-field--wide {
  grid-column: span 2;
}

.area-detail-view__edit-field label {
  font-size: 0.78rem;
  font-weight: 700;
  color: var(--ink-soft);
}

.area-detail-view__edit-field input,
.area-detail-view__edit-field textarea {
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

.area-detail-view__edit-field input:focus,
.area-detail-view__edit-field textarea:focus {
  outline: none;
  border-color: var(--brand-500);
  box-shadow: 0 0 0 3px oklch(56% 0.1 146 / 0.14);
}

@media (max-width: 640px) {
  .area-detail-view__edit-grid {
    grid-template-columns: 1fr;
  }

  .area-detail-view__edit-field--wide {
    grid-column: span 1;
  }
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

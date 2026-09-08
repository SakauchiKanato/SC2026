<!--
  地域の特色詳細ページ（PDF「地域詳細（企業・自治体向け）」の確認画面／編集画面に準拠）。

  ・確認モード（デフォルト）：ライフスタイルデータは閲覧のみ。
    「企業・自治体が実現したいこと」は、地域を登録していない企業・自治体も含め
    誰でも・何件でも投稿できる掲示板として、全団体の投稿を一覧表示する
    （自団体の投稿は色分け＋「（あなたの団体）」表示。自団体の投稿はここから直接編集・削除できる）。
  ・編集モード：地域の登録者本人（企業・自治体アカウント）のみ、ライフスタイルデータを編集できる。
  ・発案者（role=user）には引き続き「アイデア登録はこちらから→」の導線を表示する。
-->
<template>
  <section class="mt-page area-detail-view">
    <p v-if="isLoading" class="idea-loading-indicator">読み込み中です…</p>
    <p v-else-if="loadError" class="idea-banner idea-banner--error">{{ loadError }}</p>

    <template v-else-if="area">
      <div class="area-detail-view__header">
        <div>
          <h1 class="mt-page__title">{{ area.name }}の特色{{ mode === 'edit' ? 'を編集' : '' }}</h1>
          <p v-if="tagLabel" class="area-detail-view__tags">{{ tagLabel }}</p>
        </div>
        <span v-if="mode === 'edit'" class="mt-pill mt-pill--tan area-detail-view__mode-badge">
          ✎ 編集モード
        </span>
      </div>

      <div class="area-detail-view__columns">
        <!-- ライフスタイルデータ -->
        <div class="area-detail-view__column">
          <div class="area-detail-view__column-head">
            <p class="area-detail-view__section-label">ライフスタイルデータ</p>
            <span class="area-detail-view__hint">
              {{ mode === 'edit' ? '編集可能' : '参考データ（イメージ）' }}
            </span>
          </div>

          <form
            v-if="mode === 'edit'"
            class="mt-box area-detail-view__lifestyle-form"
            @submit.prevent="handleSaveLifestyle"
          >
            <div class="mt-form-row">
              <div class="mt-form-field">
                <label for="edit-population">人口</label>
                <input
                  id="edit-population"
                  v-model="lifestyleForm.population"
                  type="text"
                  maxlength="255"
                  placeholder="例：約22.6万人"
                />
              </div>
              <div class="mt-form-field">
                <label for="edit-day-night-population-ratio">昼夜人口比率</label>
                <input
                  id="edit-day-night-population-ratio"
                  v-model="lifestyleForm.dayNightPopulationRatio"
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
                  v-model="lifestyleForm.averageAge"
                  type="text"
                  maxlength="255"
                  placeholder="例：38.4歳"
                />
              </div>
              <div class="mt-form-field">
                <label for="edit-main-industry">主要産業</label>
                <input
                  id="edit-main-industry"
                  v-model="lifestyleForm.mainIndustry"
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
                v-model="lifestyleForm.transitAccess"
                type="text"
                maxlength="255"
                placeholder="例：JR山手線・私鉄5路線が乗り入れる広域ターミナル"
              />
            </div>

            <div class="mt-form-field">
              <label for="edit-address">住所</label>
              <input
                id="edit-address"
                v-model="lifestyleForm.address"
                type="text"
                maxlength="255"
                placeholder="例：東京都渋谷区宇田川町1-1"
              />
            </div>

            <div class="mt-form-field">
              <label for="edit-other">その他</label>
              <textarea
                id="edit-other"
                v-model="lifestyleForm.other"
                rows="3"
                maxlength="1000"
                placeholder="その他、地域の特色があれば記入してください"
              ></textarea>
            </div>
          </form>

          <div v-else class="area-detail-view__lifestyle-grid">
            <div class="mt-box">
              <p class="area-detail-view__lifestyle-label">人口</p>
              <p>{{ area.population || '（未記入）' }}</p>
            </div>
            <div class="mt-box">
              <p class="area-detail-view__lifestyle-label">昼夜人口比率</p>
              <p>{{ area.day_night_population_ratio || '（未記入）' }}</p>
            </div>
            <div class="mt-box">
              <p class="area-detail-view__lifestyle-label">平均年齢</p>
              <p>{{ area.average_age || '（未記入）' }}</p>
            </div>
            <div class="mt-box">
              <p class="area-detail-view__lifestyle-label">主要産業</p>
              <p>{{ area.main_industry || '（未記入）' }}</p>
            </div>
            <div class="mt-box area-detail-view__lifestyle-grid__wide">
              <p class="area-detail-view__lifestyle-label">交通アクセス</p>
              <p>{{ area.transit_access || '（未記入）' }}</p>
            </div>
            <div class="mt-box area-detail-view__lifestyle-grid__wide">
              <p class="area-detail-view__lifestyle-label">住所</p>
              <p>{{ area.address || '（未記入）' }}</p>
            </div>
            <div v-if="area.other" class="mt-box area-detail-view__lifestyle-grid__wide">
              <p class="area-detail-view__lifestyle-label">その他</p>
              <p class="area-detail-view__other-text">{{ area.other }}</p>
            </div>
          </div>
        </div>

        <!-- 企業・自治体が実現したいこと -->
        <div class="area-detail-view__column">
          <div class="area-detail-view__column-head">
            <p class="area-detail-view__section-label">企業・自治体が実現したいこと</p>
            <span class="area-detail-view__hint">{{ challenges.length }}件掲載中</span>
          </div>

          <p v-if="isChallengesLoading" class="idea-loading-indicator">読み込み中です…</p>

          <div v-else class="area-detail-view__challenge-list">
            <p v-if="challenges.length === 0" class="mt-box mt-box--placeholder">まだ投稿がありません</p>

            <div
              v-for="entry in orderedChallenges"
              :key="entry.id"
              class="mt-box area-detail-view__challenge-card"
              :class="{ 'area-detail-view__challenge-card--own': isOwnEntry(entry) }"
            >
              <template v-if="editingEntryId === entry.id">
                <div class="mt-form-field">
                  <label :for="`edit-entry-challenges-${entry.id}`">課題点・問題点</label>
                  <textarea
                    :id="`edit-entry-challenges-${entry.id}`"
                    v-model="entryForm.challenges"
                    rows="3"
                    maxlength="2000"
                  ></textarea>
                </div>
                <div class="mt-form-field">
                  <label :for="`edit-entry-expected-future-${entry.id}`">期待する未来</label>
                  <textarea
                    :id="`edit-entry-expected-future-${entry.id}`"
                    v-model="entryForm.expectedFuture"
                    rows="3"
                    maxlength="2000"
                  ></textarea>
                </div>
                <p v-if="entryError" class="idea-banner idea-banner--error">{{ entryError }}</p>
                <div class="area-detail-view__entry-actions">
                  <button
                    type="button"
                    class="mt-pill mt-pill--tan"
                    :disabled="isEntrySaving"
                    @click="handleUpdateEntry(entry)"
                  >
                    {{ isEntrySaving ? '保存中...' : '保存する' }}
                  </button>
                  <button type="button" class="area-detail-view__text-button" @click="cancelEntryEdit">
                    キャンセル
                  </button>
                </div>
              </template>

              <template v-else>
                <div class="area-detail-view__challenge-card-head">
                  <span
                    class="area-detail-view__org-badge"
                    :class="{ 'area-detail-view__org-badge--own': isOwnEntry(entry) }"
                  >
                    {{ entry.user_name }}{{ isOwnEntry(entry) ? '（あなたの団体）' : '' }}
                  </span>
                  <div v-if="isOwnEntry(entry)" class="area-detail-view__entry-controls">
                    <button
                      type="button"
                      class="area-detail-view__icon-button"
                      title="編集する"
                      @click="startEntryEdit(entry)"
                    >
                      ✎
                    </button>
                    <button
                      type="button"
                      class="area-detail-view__icon-button"
                      title="削除する"
                      @click="handleDeleteEntry(entry)"
                    >
                      🗑
                    </button>
                  </div>
                </div>
                <p class="area-detail-view__challenge-label">課題点・問題点</p>
                <p class="area-detail-view__challenge-text">{{ entry.challenges }}</p>
                <p class="area-detail-view__challenge-label">期待する未来</p>
                <p class="area-detail-view__challenge-text">{{ entry.expected_future }}</p>
              </template>
            </div>
          </div>

          <div v-if="user?.role === 'company'" class="area-detail-view__add-entry">
            <div v-if="isAddingEntry" class="mt-box">
              <div class="mt-form-field">
                <label for="new-entry-challenges">課題点・問題点</label>
                <textarea
                  id="new-entry-challenges"
                  v-model="newEntryForm.challenges"
                  rows="3"
                  maxlength="2000"
                  placeholder="例：若者世代の地域行事への参加率が低下している"
                ></textarea>
              </div>
              <div class="mt-form-field">
                <label for="new-entry-expected-future">期待する未来</label>
                <textarea
                  id="new-entry-expected-future"
                  v-model="newEntryForm.expectedFuture"
                  rows="3"
                  maxlength="2000"
                  placeholder="例：若者世代が日常的に地域活動へ参加する仕組みができている"
                ></textarea>
              </div>
              <p v-if="newEntryError" class="idea-banner idea-banner--error">{{ newEntryError }}</p>
              <div class="area-detail-view__entry-actions">
                <button
                  type="button"
                  class="mt-pill mt-pill--tan"
                  :disabled="isNewEntrySaving"
                  @click="handleCreateEntry"
                >
                  {{ isNewEntrySaving ? '投稿中...' : '追加する' }}
                </button>
                <button type="button" class="area-detail-view__text-button" @click="cancelAddEntry">
                  キャンセル
                </button>
              </div>
            </div>
            <button v-else type="button" class="area-detail-view__add-button" @click="startAddEntry">
              ＋ 実現したいことを追加する
            </button>
          </div>
        </div>
      </div>

      <p v-if="saveError" class="idea-banner idea-banner--error">{{ saveError }}</p>

      <div v-if="mode === 'edit'" class="mt-box area-detail-view__banner">
        <div>
          <p class="area-detail-view__banner-title">変更を保存する</p>
          <p class="area-detail-view__banner-desc">ライフスタイルデータを更新します。</p>
        </div>
        <div class="area-detail-view__banner-actions">
          <button type="button" class="area-detail-view__text-button" @click="cancelEdit">
            キャンセル
          </button>
          <button
            type="button"
            class="mt-pill mt-pill--tan"
            :disabled="isSaving"
            @click="handleSaveLifestyle"
          >
            {{ isSaving ? '保存中...' : '変更を保存する' }}
          </button>
        </div>
      </div>

      <div v-else-if="isOwner" class="mt-box area-detail-view__banner">
        <div>
          <p class="area-detail-view__banner-title">この地域の情報を編集しますか？</p>
          <p class="area-detail-view__banner-desc">ライフスタイルデータを変更できます。</p>
        </div>
        <button type="button" class="mt-pill mt-pill--tan" @click="startEdit">✎ 編集する</button>
      </div>

      <!-- アイデア登録は発案者（role=user）専用。企業・自治体が他社の地域を見ている場合は表示しない -->
      <div v-else-if="user?.role === 'user'" class="area-detail-view__actions">
        <RouterLink :to="{ name: 'idea-new', params: { areaId: area.id } }" class="mt-pill mt-pill--tan">
          アイデア登録はこちらから→
        </RouterLink>
      </div>
    </template>
  </section>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import {
  fetchArea,
  updateArea,
  fetchAreaChallenges,
  createAreaChallenge,
  updateAreaChallenge,
  deleteAreaChallenge,
} from '@/api/area'
import { useAuthStore } from '../../store/auth'

const props = defineProps({
  id: {
    type: [String, Number],
    required: true,
  },
})

const { user, isAuthenticated } = useAuthStore()

const area = ref(null)
const isLoading = ref(true)
const loadError = ref('')

const challenges = ref([])
const isChallengesLoading = ref(true)

// 'confirm'（確認モード）と 'edit'（編集モード。地域の登録者本人のみ遷移可）を
// クライアント側でトグルする。ページ遷移は伴わない。
const mode = ref('confirm')

const isSaving = ref(false)
const saveError = ref('')

const lifestyleForm = reactive({
  address: '',
  population: '',
  dayNightPopulationRatio: '',
  averageAge: '',
  mainIndustry: '',
  transitAccess: '',
  other: '',
})

// PostgreSQL(PDO)からのuser_idは文字列で返るため、数値化して比較する
const isOwner = computed(
  () =>
    isAuthenticated.value &&
    area.value &&
    Number(user.value?.id) === Number(area.value.user_id)
)

const tagLabel = computed(() => (area.value?.tags || []).map((tag) => tag.name).join(' × '))

// 自団体の投稿を先頭に、それ以外は投稿順（古い順）で並べる
const orderedChallenges = computed(() => [
  ...challenges.value.filter((entry) => isOwnEntry(entry)),
  ...challenges.value.filter((entry) => !isOwnEntry(entry)),
])

function isOwnEntry(entry) {
  return isAuthenticated.value && Number(user.value?.id) === Number(entry.user_id)
}

function resetLifestyleForm() {
  lifestyleForm.address = area.value.address ?? ''
  lifestyleForm.population = area.value.population ?? ''
  lifestyleForm.dayNightPopulationRatio = area.value.day_night_population_ratio ?? ''
  lifestyleForm.averageAge = area.value.average_age ?? ''
  lifestyleForm.mainIndustry = area.value.main_industry ?? ''
  lifestyleForm.transitAccess = area.value.transit_access ?? ''
  lifestyleForm.other = area.value.other ?? ''
}

async function loadArea() {
  isLoading.value = true
  loadError.value = ''
  try {
    area.value = await fetchArea(props.id)
    resetLifestyleForm()
  } catch (error) {
    loadError.value = '地域データの取得に失敗しました'
  } finally {
    isLoading.value = false
  }
}

async function loadChallenges() {
  isChallengesLoading.value = true
  try {
    challenges.value = await fetchAreaChallenges(props.id)
  } catch (error) {
    // 掲示板の取得に失敗しても地域の基本情報は表示できるようにする
    challenges.value = []
  } finally {
    isChallengesLoading.value = false
  }
}

function startEdit() {
  if (!isOwner.value) return
  resetLifestyleForm()
  saveError.value = ''
  mode.value = 'edit'
}

function cancelEdit() {
  resetLifestyleForm()
  saveError.value = ''
  mode.value = 'confirm'
}

async function handleSaveLifestyle() {
  isSaving.value = true
  saveError.value = ''

  const payload = {
    name: area.value.name,
    address: lifestyleForm.address === '' ? null : lifestyleForm.address,
    population: lifestyleForm.population === '' ? null : lifestyleForm.population,
    day_night_population_ratio:
      lifestyleForm.dayNightPopulationRatio === '' ? null : lifestyleForm.dayNightPopulationRatio,
    average_age: lifestyleForm.averageAge === '' ? null : lifestyleForm.averageAge,
    main_industry: lifestyleForm.mainIndustry === '' ? null : lifestyleForm.mainIndustry,
    transit_access: lifestyleForm.transitAccess === '' ? null : lifestyleForm.transitAccess,
    other: lifestyleForm.other === '' ? null : lifestyleForm.other,
    tags: (area.value.tags || []).map((tag) => tag.name),
  }

  try {
    area.value = await updateArea(props.id, payload)
    resetLifestyleForm()
    mode.value = 'confirm'
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

// --- 「企業・自治体が実現したいこと」の新規投稿（company権限であれば地域の登録者でなくても可） ---
const isAddingEntry = ref(false)
const newEntryForm = reactive({ challenges: '', expectedFuture: '' })
const newEntryError = ref('')
const isNewEntrySaving = ref(false)

function startAddEntry() {
  newEntryForm.challenges = ''
  newEntryForm.expectedFuture = ''
  newEntryError.value = ''
  isAddingEntry.value = true
}

function cancelAddEntry() {
  isAddingEntry.value = false
  newEntryError.value = ''
}

async function handleCreateEntry() {
  isNewEntrySaving.value = true
  newEntryError.value = ''
  try {
    const created = await createAreaChallenge(props.id, {
      challenges: newEntryForm.challenges,
      expected_future: newEntryForm.expectedFuture,
    })
    challenges.value = [...challenges.value, created]
    isAddingEntry.value = false
  } catch (error) {
    if (error.status === 403) {
      newEntryError.value = 'この投稿は企業・自治体アカウントのみ行えます'
    } else if (error.status === 422 && error.errors) {
      newEntryError.value = error.errors.join(' / ')
    } else {
      newEntryError.value = '投稿に失敗しました'
    }
  } finally {
    isNewEntrySaving.value = false
  }
}

// --- 自団体の投稿の編集・削除（投稿の作成者本人のみ。地域の登録者かどうかは問わない） ---
const editingEntryId = ref(null)
const entryForm = reactive({ challenges: '', expectedFuture: '' })
const entryError = ref('')
const isEntrySaving = ref(false)

function startEntryEdit(entry) {
  editingEntryId.value = entry.id
  entryForm.challenges = entry.challenges
  entryForm.expectedFuture = entry.expected_future
  entryError.value = ''
}

function cancelEntryEdit() {
  editingEntryId.value = null
  entryError.value = ''
}

async function handleUpdateEntry(entry) {
  isEntrySaving.value = true
  entryError.value = ''
  try {
    const updated = await updateAreaChallenge(entry.id, {
      challenges: entryForm.challenges,
      expected_future: entryForm.expectedFuture,
    })
    challenges.value = challenges.value.map((item) => (item.id === entry.id ? updated : item))
    editingEntryId.value = null
  } catch (error) {
    if (error.status === 403) {
      entryError.value = 'この投稿を編集する権限がありません'
    } else if (error.status === 422 && error.errors) {
      entryError.value = error.errors.join(' / ')
    } else {
      entryError.value = '保存に失敗しました'
    }
  } finally {
    isEntrySaving.value = false
  }
}

async function handleDeleteEntry(entry) {
  if (!window.confirm('この投稿を削除しますか？')) {
    return
  }
  try {
    await deleteAreaChallenge(entry.id)
    challenges.value = challenges.value.filter((item) => item.id !== entry.id)
  } catch (error) {
    saveError.value = '投稿の削除に失敗しました'
  }
}

onMounted(() => {
  loadArea()
  loadChallenges()
})
</script>

<style scoped>
.area-detail-view__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: var(--idea-spacing-md);
  flex-wrap: wrap;
  margin-bottom: var(--idea-spacing-lg);
}

.area-detail-view__tags {
  margin: 6px 0 0;
  color: var(--mt-color-green-dark);
  font-weight: 700;
  font-size: 0.9rem;
}

.area-detail-view__mode-badge {
  cursor: default;
}

.area-detail-view__columns {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--idea-spacing-lg);
  align-items: start;
}

@media (max-width: 800px) {
  .area-detail-view__columns {
    grid-template-columns: 1fr;
  }
}

.area-detail-view__column-head {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: var(--idea-spacing-sm);
  margin-bottom: var(--idea-spacing-sm);
}

.area-detail-view__section-label {
  font-weight: 700;
  margin: 0;
}

.area-detail-view__hint {
  font-size: 0.8rem;
  color: var(--mt-color-text-muted);
  white-space: nowrap;
}

.area-detail-view__lifestyle-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--idea-spacing-sm);
}

.area-detail-view__lifestyle-grid__wide {
  grid-column: 1 / -1;
}

@media (max-width: 640px) {
  .area-detail-view__lifestyle-grid {
    grid-template-columns: 1fr;
  }
}

.area-detail-view__lifestyle-label {
  font-weight: 700;
  margin: 0 0 4px;
  font-size: 0.85rem;
  color: var(--mt-color-text-muted);
}

.area-detail-view__lifestyle-form {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.area-detail-view__other-text {
  white-space: pre-wrap;
}

.area-detail-view__challenge-list {
  display: flex;
  flex-direction: column;
  gap: var(--idea-spacing-sm);
}

.area-detail-view__challenge-card {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.area-detail-view__challenge-card--own {
  background: var(--mt-color-green-pale);
  border-color: var(--mt-color-green-dark);
}

.area-detail-view__challenge-card-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--idea-spacing-sm);
}

.area-detail-view__org-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: var(--mt-radius-pill);
  background: var(--mt-color-tan);
  font-size: 0.8rem;
  font-weight: 700;
}

.area-detail-view__org-badge--own {
  background: var(--mt-color-green);
  color: #16240c;
}

.area-detail-view__entry-controls {
  display: flex;
  gap: 6px;
}

.area-detail-view__icon-button {
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 1rem;
  line-height: 1;
  padding: 4px;
  color: var(--mt-color-text-muted);
}

.area-detail-view__icon-button:hover {
  color: var(--mt-color-text);
}

.area-detail-view__challenge-label {
  margin: 4px 0 0;
  font-size: 0.8rem;
  color: var(--mt-color-text-muted);
}

.area-detail-view__challenge-text {
  margin: 0;
  white-space: pre-wrap;
}

.area-detail-view__entry-actions {
  display: flex;
  align-items: center;
  gap: var(--idea-spacing-md);
  margin-top: var(--idea-spacing-sm);
}

.area-detail-view__text-button {
  border: none;
  background: transparent;
  color: var(--mt-color-text-muted);
  text-decoration: underline;
  cursor: pointer;
  font: inherit;
  padding: 0;
}

.area-detail-view__add-entry {
  margin-top: var(--idea-spacing-sm);
}

.area-detail-view__add-button {
  width: 100%;
  border: 1px dashed var(--mt-color-border);
  border-radius: var(--mt-radius-card);
  background: transparent;
  color: var(--mt-color-text-muted);
  padding: 14px;
  cursor: pointer;
  font: inherit;
  font-weight: 700;
}

.area-detail-view__add-button:hover {
  color: var(--mt-color-text);
  border-color: var(--mt-color-text-muted);
}

.area-detail-view__banner {
  margin-top: var(--idea-spacing-lg);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--idea-spacing-md);
  flex-wrap: wrap;
}

.area-detail-view__banner-title {
  margin: 0 0 4px;
  font-weight: 700;
}

.area-detail-view__banner-desc {
  margin: 0;
  color: var(--mt-color-text-muted);
  font-size: 0.9rem;
}

.area-detail-view__banner-actions {
  display: flex;
  align-items: center;
  gap: var(--idea-spacing-md);
}

.area-detail-view__actions {
  margin-top: var(--idea-spacing-md);
  display: flex;
  justify-content: flex-end;
}
</style>

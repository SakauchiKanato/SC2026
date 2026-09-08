<!--
  地域の特色詳細ページ。RegionDetailCompanyView.dc.html（確認モード）・
  RegionDetailEdit.dc.html（編集モード）準拠。

  ・確認モード（デフォルト）：ライフスタイルデータは閲覧のみ。
    「企業・自治体が実現したいこと」は、地域を登録していない企業・自治体も含め
    誰でも・何件でも投稿できる掲示板として、全団体の投稿を一覧表示する
    （自団体の投稿は色分け＋「（あなたの団体）」表示。自団体の投稿はここから直接編集・削除できる）。
  ・編集モード：地域の登録者本人（企業・自治体アカウント）のみ、ライフスタイルデータを編集できる。
  ・発案者（role=user）には引き続き「アイデア登録はこちらから→」の導線を表示する。

  NOTE: 掲示板（投稿の取得・作成・編集・削除）とライフスタイルデータの保存ロジックは
        チームメイトが実装したものをそのまま使用し、テンプレート／スタイルのみ
        machitane-design準拠に刷新した（挙動は変更していない）。
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
          {{ area.name }}の特色<template v-if="mode === 'edit'">を編集</template>
        </h1>
        <span v-if="mode === 'edit'" class="area-detail-view__edit-badge">
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
      <p v-if="tagLabel" class="area-detail-view__tags">{{ tagLabel }}</p>

      <div class="area-detail-view__grid">
        <!-- ライフスタイルデータ -->
        <section class="area-detail-view__panel">
          <div class="area-detail-view__panel-head">
            <h2 class="area-detail-view__panel-title">ライフスタイルデータ</h2>
            <span class="area-detail-view__panel-note">
              {{ mode === 'edit' ? '編集可能' : '参考データ（イメージ）' }}
            </span>
          </div>

          <form v-if="mode === 'edit'" class="area-detail-view__edit-grid" @submit.prevent="handleSaveLifestyle">
            <div class="area-detail-view__edit-field">
              <label for="edit-population">人口</label>
              <input
                id="edit-population"
                v-model="lifestyleForm.population"
                type="text"
                maxlength="255"
                placeholder="例：約22.6万人"
              />
            </div>
            <div class="area-detail-view__edit-field">
              <label for="edit-day-night-population-ratio">昼夜人口比率</label>
              <input
                id="edit-day-night-population-ratio"
                v-model="lifestyleForm.dayNightPopulationRatio"
                type="text"
                maxlength="255"
                placeholder="例：約230%"
              />
            </div>
            <div class="area-detail-view__edit-field">
              <label for="edit-average-age">平均年齢</label>
              <input
                id="edit-average-age"
                v-model="lifestyleForm.averageAge"
                type="text"
                maxlength="255"
                placeholder="例：38.4歳"
              />
            </div>
            <div class="area-detail-view__edit-field">
              <label for="edit-main-industry">主要産業</label>
              <input
                id="edit-main-industry"
                v-model="lifestyleForm.mainIndustry"
                type="text"
                maxlength="255"
                placeholder="例：商業・サービス業 / IT"
              />
            </div>
            <div class="area-detail-view__edit-field area-detail-view__edit-field--wide">
              <label for="edit-transit-access">交通アクセス</label>
              <input
                id="edit-transit-access"
                v-model="lifestyleForm.transitAccess"
                type="text"
                maxlength="255"
                placeholder="例：JR山手線・私鉄5路線が乗り入れる広域ターミナル"
              />
            </div>
            <div class="area-detail-view__edit-field area-detail-view__edit-field--wide">
              <label for="edit-address">住所</label>
              <input
                id="edit-address"
                v-model="lifestyleForm.address"
                type="text"
                maxlength="255"
                placeholder="例：東京都渋谷区宇田川町1-1"
              />
            </div>
            <div class="area-detail-view__edit-field area-detail-view__edit-field--wide">
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

          <div v-else class="area-detail-view__stat-grid">
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
            <div class="area-detail-view__stat area-detail-view__stat--wide">
              <p class="area-detail-view__stat-label">交通アクセス</p>
              <p class="area-detail-view__stat-value">{{ area.transit_access || '（未記入）' }}</p>
            </div>
            <div class="area-detail-view__stat area-detail-view__stat--wide">
              <p class="area-detail-view__stat-label">住所</p>
              <p class="area-detail-view__stat-value">{{ area.address || '（未記入）' }}</p>
            </div>
            <div v-if="area.other" class="area-detail-view__stat area-detail-view__stat--wide">
              <p class="area-detail-view__stat-label">その他</p>
              <p class="area-detail-view__stat-value area-detail-view__stat-value--text">{{ area.other }}</p>
            </div>
          </div>
        </section>

        <!-- 企業・自治体が実現したいこと（掲示板） -->
        <section class="area-detail-view__panel">
          <div class="area-detail-view__panel-head">
            <h2 class="area-detail-view__panel-title">企業・自治体が<br />実現したいこと</h2>
            <span class="area-detail-view__panel-note">{{ challenges.length }}件掲載中</span>
          </div>

          <p v-if="isChallengesLoading" class="idea-loading-indicator">読み込み中です…</p>

          <div v-else class="area-detail-view__board">
            <p v-if="challenges.length === 0" class="idea-empty-state">まだ投稿がありません</p>

            <div
              v-for="entry in orderedChallenges"
              :key="entry.id"
              class="area-detail-view__challenge-card"
              :class="{ 'area-detail-view__challenge-card--own': isOwnEntry(entry) }"
            >
              <template v-if="editingEntryId === entry.id">
                <div class="area-detail-view__edit-field">
                  <label :for="`edit-entry-challenges-${entry.id}`">課題点・問題点</label>
                  <textarea
                    :id="`edit-entry-challenges-${entry.id}`"
                    v-model="entryForm.challenges"
                    rows="3"
                    maxlength="2000"
                  ></textarea>
                </div>
                <div class="area-detail-view__edit-field">
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
                    class="area-detail-view__cta-btn area-detail-view__cta-btn--sm"
                    :disabled="isEntrySaving"
                    @click="handleUpdateEntry(entry)"
                  >
                    {{ isEntrySaving ? '保存中...' : '保存する' }}
                  </button>
                  <button type="button" class="area-detail-view__idea-cancel" @click="cancelEntryEdit">
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
                <p class="area-detail-view__request-label">課題点・問題点</p>
                <p class="area-detail-view__request-text">{{ entry.challenges }}</p>
                <p class="area-detail-view__request-label">期待する未来</p>
                <p class="area-detail-view__request-text">{{ entry.expected_future }}</p>
              </template>
            </div>
          </div>

          <div v-if="user?.role === 'company'" class="area-detail-view__add-entry">
            <div v-if="isAddingEntry" class="area-detail-view__challenge-card">
              <div class="area-detail-view__edit-field">
                <label for="new-entry-challenges">課題点・問題点</label>
                <textarea
                  id="new-entry-challenges"
                  v-model="newEntryForm.challenges"
                  rows="3"
                  maxlength="2000"
                  placeholder="例：若者世代の地域行事への参加率が低下している"
                ></textarea>
              </div>
              <div class="area-detail-view__edit-field">
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
                  class="area-detail-view__cta-btn area-detail-view__cta-btn--sm"
                  :disabled="isNewEntrySaving"
                  @click="handleCreateEntry"
                >
                  {{ isNewEntrySaving ? '投稿中...' : '追加する' }}
                </button>
                <button type="button" class="area-detail-view__idea-cancel" @click="cancelAddEntry">
                  キャンセル
                </button>
              </div>
            </div>
            <button v-else type="button" class="area-detail-view__add-button" @click="startAddEntry">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                <path d="M12 5v14M5 12h14" stroke-width="2.2" stroke-linecap="round" />
              </svg>
              実現したいことを追加する
            </button>
          </div>
        </section>
      </div>

      <p v-if="saveError" class="idea-banner idea-banner--error">{{ saveError }}</p>

      <div v-if="mode === 'edit'" class="area-detail-view__idea-panel">
        <div class="area-detail-view__idea-prompt">
          <div>
            <p class="area-detail-view__idea-prompt-title">変更を保存する</p>
            <p class="area-detail-view__idea-prompt-text">ライフスタイルデータを更新します。</p>
          </div>
          <div class="area-detail-view__banner-actions">
            <button type="button" class="area-detail-view__idea-cancel" @click="cancelEdit">キャンセル</button>
            <button type="button" class="area-detail-view__cta-btn" :disabled="isSaving" @click="handleSaveLifestyle">
              {{ isSaving ? '保存中...' : '変更を保存する' }}
            </button>
          </div>
        </div>
      </div>

      <div v-else-if="isOwner" class="area-detail-view__idea-panel">
        <div class="area-detail-view__idea-prompt">
          <div>
            <p class="area-detail-view__idea-prompt-title">この地域の情報を編集しますか？</p>
            <p class="area-detail-view__idea-prompt-text">ライフスタイルデータを変更できます。</p>
          </div>
          <button type="button" class="area-detail-view__cta-btn" @click="startEdit">
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

      <!-- アイデア登録は発案者（role=user）専用。企業・自治体が他社の地域を見ている場合は表示しない -->
      <div v-else-if="user?.role === 'user'" class="area-detail-view__idea-panel">
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

const challenges = ref([])
const isChallengesLoading = ref(true)

// 'confirm'（確認モード）と 'edit'（編集モード。地域の登録者本人のみ遷移可）を
// クライアント側でトグルする。ページ遷移は伴わない。
const mode = ref('confirm')

const isSaving = ref(false)
const saveError = ref('')

// 発案者はarea-list（企業・自治体専用）を持たないため「地域一覧」代わりにhomeへ、
// 企業・自治体はarea-listへ戻す
const backLinkRoute = computed(() =>
  user.value?.role === 'company' ? { name: 'area-list' } : { name: 'home' },
)

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

onMounted(() => {
  loadArea()
  loadChallenges()
})
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

.area-detail-view__tags {
  margin: 0 0 var(--idea-spacing-md);
  color: var(--brand-700);
  font-weight: 700;
  font-size: 0.85rem;
}

.area-detail-view__grid {
  display: grid;
  grid-template-columns: 1.6fr 1fr;
  gap: var(--idea-spacing-md);
  align-items: start;
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

.area-detail-view__stat-value--text {
  font-family: inherit;
  font-size: 0.9rem;
  white-space: pre-wrap;
}

@media (max-width: 640px) {
  .area-detail-view__stat-grid {
    grid-template-columns: 1fr;
  }

  .area-detail-view__stat--wide {
    grid-column: span 1;
  }
}

/* オーナー編集フォームの入力欄グリッド（ライフスタイルデータ／掲示板の投稿フォーム共通） */
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

/* 「企業・自治体が実現したいこと」掲示板 */
.area-detail-view__board {
  display: flex;
  flex-direction: column;
  gap: var(--idea-spacing-md);
}

.area-detail-view__challenge-card {
  padding: 18px;
  background: var(--bg);
  border: 1px solid var(--line);
  border-radius: 16px;
}

.area-detail-view__challenge-card--own {
  background: var(--surface-sage-soft);
  border-color: transparent;
}

.area-detail-view__challenge-card-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--idea-spacing-sm);
  margin-bottom: 14px;
}

.area-detail-view__org-badge {
  display: inline-flex;
  font-size: 0.72rem;
  font-weight: 700;
  color: var(--ink-soft);
  background: var(--surface);
  padding: 4px 12px;
  border-radius: var(--mt-radius-pill);
}

.area-detail-view__org-badge--own {
  color: var(--brand-900);
  background: oklch(99% 0.005 90 / 0.6);
}

.area-detail-view__entry-controls {
  display: flex;
  gap: 4px;
}

.area-detail-view__icon-button {
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 1rem;
  line-height: 1;
  padding: 4px;
  color: var(--ink-faint);
}

.area-detail-view__icon-button:hover {
  color: var(--ink);
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
  margin: 0 0 12px;
  white-space: pre-wrap;
}

.area-detail-view__entry-actions {
  display: flex;
  align-items: center;
  gap: var(--idea-spacing-md);
  margin-top: 8px;
}

.area-detail-view__add-entry {
  margin-top: var(--idea-spacing-md);
}

.area-detail-view__add-button {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 13px;
  border: 1.5px dashed var(--line);
  border-radius: 14px;
  background: transparent;
  color: var(--brand-700);
  stroke: var(--brand-700);
  cursor: pointer;
  font: inherit;
  font-weight: 700;
  font-size: 0.85rem;
}

.area-detail-view__add-button:hover {
  border-color: var(--brand-500);
}

/* インライン展開式のパネル（アイデア登録・保存バナー・編集バナー共通） */
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

.area-detail-view__banner-actions {
  display: flex;
  align-items: center;
  gap: var(--idea-spacing-md);
  flex-shrink: 0;
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

.area-detail-view__cta-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.area-detail-view__cta-btn--sm {
  padding: 10px 20px;
  font-size: 0.82rem;
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

.area-detail-view__entry-actions .area-detail-view__idea-cancel {
  margin-top: 0;
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

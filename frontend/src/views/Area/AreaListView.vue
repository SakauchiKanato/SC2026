<template>
  <div class="area-list-view">
    <div class="area-list-view__header">
      <h2>地域一覧</h2>
      <router-link to="/areas/new" class="area-list-view__new-button">
        + 新規登録
      </router-link>
    </div>

    <p v-if="isLoading">読み込み中...</p>
    <p v-else-if="loadError" class="area-list-view__error">{{ loadError }}</p>
    <p v-else-if="areas.length === 0">登録されている地域がありません</p>

    <AreaCard
      v-for="area in areas"
      :key="area.id"
      :area="area"
      @edit="goToEdit"
      @delete="confirmDelete"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AreaCard from '@/components/Area/AreaCard.vue';
import { fetchAreas, deleteArea } from '@/api/area';

const router = useRouter();

const areas = ref([]);
const isLoading = ref(true);
const loadError = ref('');

async function loadAreas() {
  isLoading.value = true;
  loadError.value = '';

  try {
    areas.value = await fetchAreas();
  } catch (error) {
    loadError.value = '地域一覧の取得に失敗しました';
  } finally {
    isLoading.value = false;
  }
}

function goToEdit(id) {
  router.push(`/areas/${id}/edit`);
}

async function confirmDelete(id) {
  const isConfirmed = window.confirm('この地域を削除しますか？この操作は取り消せません。');
  if (!isConfirmed) {
    return;
  }

  try {
    await deleteArea(id);
    areas.value = areas.value.filter((area) => area.id !== id);
  } catch (error) {
    window.alert('削除に失敗しました');
  }
}

onMounted(loadAreas);
</script>

<style scoped>
.area-list-view__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.area-list-view__new-button {
  text-decoration: none;
  padding: 8px 16px;
  background-color: #2c7a4b;
  color: #fff;
  border-radius: 4px;
}

.area-list-view__error {
  color: #c0392b;
}
</style>

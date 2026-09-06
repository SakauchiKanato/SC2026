<template>
  <div class="area-card">
    <div class="area-card__header">
      <h3 class="area-card__name">{{ area.name }}</h3>
      <span class="area-card__location">{{ area.prefecture }} {{ area.city }}</span>
    </div>
    <p class="area-card__features">{{ area.features }}</p>
    <p v-if="hasCoordinates" class="area-card__coordinates">
      緯度: {{ area.latitude }} / 経度: {{ area.longitude }}
    </p>
    <div class="area-card__actions">
      <button type="button" @click="$emit('edit', area.id)">編集</button>
      <button type="button" class="area-card__delete" @click="$emit('delete', area.id)">
        削除
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  area: {
    type: Object,
    required: true,
  },
});

defineEmits(['edit', 'delete']);

const hasCoordinates = computed(
  () => props.area.latitude !== null && props.area.longitude !== null
);
</script>

<style scoped>
.area-card {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 12px;
}

.area-card__header {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
}

.area-card__name {
  margin: 0;
  font-size: 1.1rem;
}

.area-card__location {
  color: #666;
  font-size: 0.9rem;
}

.area-card__features {
  white-space: pre-wrap;
  color: #333;
}

.area-card__coordinates {
  font-size: 0.85rem;
  color: #888;
}

.area-card__actions {
  display: flex;
  gap: 8px;
  margin-top: 8px;
}

.area-card__delete {
  color: #c0392b;
}
</style>

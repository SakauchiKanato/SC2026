<script setup>
defineProps({
  idea: {
    type: Object,
    required: true,
  },
})

function formatDate(dateString) {
  if (!dateString) return ''
  return new Intl.DateTimeFormat('ja-JP', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(new Date(dateString))
}
</script>

<template>
  <article class="idea-card">
    <div class="idea-card__header">
      <span
        class="idea-status-badge"
        :class="idea.status === 'success' ? 'idea-status-badge--success' : 'idea-status-badge--failure'"
      >
        {{ idea.status === 'success' ? '成功' : '失敗' }}
      </span>
      <span class="idea-card__area">{{ idea.area_name }}</span>
    </div>

    <h3 class="idea-card__title">{{ idea.title }}</h3>
    <p class="idea-card__content">{{ idea.content }}</p>

    <p class="idea-card__reason">
      <span class="idea-card__reason-label">
        {{ idea.status === 'success' ? '成功要因：' : '失敗要因：' }}
      </span>
      {{ idea.reason }}
    </p>

    <p class="idea-card__date">{{ formatDate(idea.created_at) }}</p>
  </article>
</template>

<style scoped>
.idea-card {
  border: 1px solid var(--idea-color-border);
  border-radius: var(--idea-radius);
  background: var(--idea-color-surface);
  padding: var(--idea-spacing-md);
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.idea-card__header {
  display: flex;
  align-items: center;
  gap: var(--idea-spacing-sm);
}

.idea-card__area {
  font-size: 0.85rem;
  color: var(--idea-color-text-muted);
}

.idea-card__title {
  margin: 0;
  font-size: 1.1rem;
  color: var(--idea-color-text);
}

.idea-card__content {
  margin: 0;
  color: var(--idea-color-text);
  line-height: 1.6;
  white-space: pre-wrap;
}

.idea-card__reason {
  margin: 0;
  color: var(--idea-color-text-muted);
  line-height: 1.6;
  white-space: pre-wrap;
}

.idea-card__reason-label {
  font-weight: 600;
  color: var(--idea-color-text);
}

.idea-card__date {
  margin: 4px 0 0;
  font-size: 0.75rem;
  color: var(--idea-color-text-muted);
  text-align: right;
}
</style>
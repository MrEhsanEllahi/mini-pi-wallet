<template>
  <div class="toast-container" role="status">
    <div
      v-for="toast in notifications.toasts"
      :key="toast.id"
      class="toast"
      :class="`toast--${toast.type}`"
    >
      <span>{{ toast.message }}</span>
      <button type="button" class="toast__close" @click="notifications.removeToast(toast.id)">
        ×
      </button>
    </div>
  </div>
</template>

<script setup>
import { useNotificationStore } from '../stores/notifications'

const notifications = useNotificationStore()
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 1rem;
  right: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  z-index: 1050;
}

.toast {
  min-width: 240px;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15);
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.75rem;
  color: #0f172a;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  font-weight: 500;
}

.toast--success {
  border-color: #34d399;
  background: #ecfdf5;
  color: #047857;
}

.toast--info {
  border-color: #60a5fa;
  background: #eff6ff;
  color: #1d4ed8;
}

.toast--error {
  border-color: #f87171;
  background: #fef2f2;
  color: #b91c1c;
}

.toast__close {
  background: transparent;
  border: none;
  color: inherit;
  font-size: 1.1rem;
  cursor: pointer;
  padding: 0;
  line-height: 1;
}

.toast__close:hover {
  opacity: 0.8;
}
</style>
